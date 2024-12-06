<?php

namespace App\Services;

use App\CourierOrders;
use App\CourierOrderStatus;
use App\CourierRegion;
use App\Package;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PartnerCourierService
{

    public function PartnerCourier ($packages) : void
    {

        try {
            $new_packages_str = '';
            $clientID = 0;
            $delivery_amount = 0;
            $client_address = '';
            $client_phone = '';
            $packages_arr_for_update = array();
            //dd($packages);
            foreach ($packages as $key => $package){

                $client = User::where('id', $key)->first();
                //dd($client);


                $client_address = $client->address1;
                $client_phone = $client->phone1;
                $clientID = $client->id;
                $clientCity = $client->city;
                $clientRegion = $client->region;

                $packageNumbers = $package->pluck('package_id')->toArray();
                $new_packages_str = implode(', ', $packageNumbers);
                $azerpost_weight = array_sum($package->pluck('gross_weight')->toArray());

                //dd($weight);

               /* foreach ($package as $pack){
                    $total_gross_weight = $pack->gross_weight;
                    $azerpost_weight = bcadd($azerpost_weight, $total_gross_weight, 3);
                    $new_packages_str .= $pack->package_id . ',';
                    array_push($packages_arr_for_update, $pack->package_id);
                }*/
                //$new_packages_str = substr($new_packages_str, 0, -1);

                //dd($azerpost_weight);


                $now = Carbon::now();
                $tomorrow = $now->addDay();

                if ($tomorrow->isSunday()) {
                    $tomorrow = $tomorrow->addWeekday();
                }

                $area_id = null;
                $region_id = null;

                if (!empty($clientCity)){
                    //dd('empty');
                    if(mb_strtoupper($clientCity, 'UTF-8') !== mb_strtoupper('Baku', 'UTF-8') && mb_strtoupper($clientCity, 'UTF-8') !== mb_strtoupper('Baki', 'UTF-8')){
                        //dd(mb_strtoupper($clientCity));
                        $is_region = CourierRegion::where(function ($query) use ($clientCity) {
                            $query->whereRaw('UPPER(name_az) = UPPER(?)', [$clientCity])
                                ->orWhereRaw('UPPER(name_en) = UPPER(?)', [$clientCity]);
                        })->first();

                        if ($is_region){
                            $region_id = $is_region->id;
                        }

                    }
                    else{
                        $azerpost_region = DB::table('azerpost_region')
                            ->select('*')
                            ->whereRaw('UPPER(SUBSTRING_INDEX(name, " ", 1)) = UPPER(SUBSTRING_INDEX(?, " ", 1))', [$clientRegion])
                            ->where(function ($query) {
                                $query->whereNotNull('area_id')
                                    ->orWhereNotNull('region_id');
                            })
                            ->first();

                        if($azerpost_region){
                            $areaId = $azerpost_region->area_id;
                            $regionId = $azerpost_region->region_id;

                            if ($areaId){
                                $area_id = $areaId;
                            }else{
                                $region_id = $regionId;
                            }
                        }
                    }
                }

                $azerpost_track = $this->generateUniqueId();
                //dd($azerpost_track);
                $courier = new CourierOrders();
                $courier->fill([
                    'packages' => $new_packages_str,
                    'has_courier' => 0,
                    'created_by' => 1,
                    'client_id' => $clientID,
                    'address' => $client_address,
                    'phone' => $client_phone,
                    'amount' => 0,
                    'delivery_amount' => 0,
                    'total_amount' => 0,
                    'courier_payment_type_id' => 2,
                    'deliver_payment_type_id' => 2,
                    'paid' => 0,
                    'is_paid' => 1,
                    'date' => $tomorrow,
                    'last_status_id' => 13,
                    'urgent' => 0,
                    'post_zip' => $client->zip1,
                    'area_id' => $area_id,
                    'region_id' => $region_id,
                    'order_weight' => $azerpost_weight,
                    'azerpost_track' => $azerpost_track

                ]);

                $courier->save();

                Package::whereIn('id', $packageNumbers)->update([
                    'courier_order_id' => $courier->getAttribute('id'),
                    'has_courier' => 1,
                    'has_courier_by' => $clientID,
                    'has_courier_at' => \Carbon\Carbon::now(),
                    'has_courier_type' => 'user_create_order_' . $courier->getAttribute('id')
                ]);

                CourierOrderStatus::create([
                    'order_id' => $courier->getAttribute('id'),
                    'status_id' => 13,
                    'created_by' => $clientID
                ]);

                $new_packages_str = '';
                $packages_arr_for_update = array();
                $packageNumbers = array();
            }
        }catch (\Exception $exception){
            Log::info([
                'partner_courier',
                $exception
            ]);
        }

    }

    private function generateUniqueId() {
        $uniqueId = 'CB'. substr(microtime(true) * 10000, -7) . rand(111, 999) . 'P';


        return $uniqueId;
    }

}