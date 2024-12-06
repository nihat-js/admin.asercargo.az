<?php

namespace App\Http\Controllers\Api;

use App\CourierRegion;
use App\Package;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CalcPartnerAmountController extends Controller
{

    public function calculate_amount_platforms()
    {
        set_time_limit(6000);

        $area_id = null;
        $region_id = null;
        $office_amount = null;
        $amount = null;
        $weight = null;
        $clientCity =null;
        $clientRegion = null;
        $is_pick_up = null;
        $merge_arr =[];
        $amount_arr =[];

        $startDate = '2023-02-30';
        $endDate = '2023-03-02';

        $platformContract = DB::table('platform_contract_details')->get();

        $packages = Package::leftJoin('users', 'package.client_id', 'users.id')
            ->where([
                'package.partner_id' => 2,
                'package.partner_amount' => null,
            ])
            ->whereBetween('package.created_at', [$startDate, $endDate])
            ->select('package.id', 'package.internal_id', 'package.gross_weight', 'package.is_partner_pickup', 'users.city', 'users.region')
            ->get();


        dd($packages);
        foreach ($packages as $package){
           // dd($amount, $package->internal_id);
            $weight = $package->gross_weight;
            $filteredContracts = $platformContract->filter(function ($contract) use ($weight) {
                return $weight >= $contract->from_weight && $weight <= $contract->to_weight;
            });

            if ($package->is_partner_pickup == 'from_office') {
                //dd($package->is_partner_pickup);

                $office_amount = $filteredContracts->where('contract_id', 2)->first();

                $amount = ($office_amount->rate * $weight) + $office_amount->charge;
                //dd($amount);
            }
            else if($package->is_partner_pickup == 'to_home'){
               // dd('home');

                $clientCity = $package->city;
                $clientRegion = $package->region;
                    if(mb_strtoupper($clientCity, 'UTF-8') !== mb_strtoupper('Baku', 'UTF-8') && mb_strtoupper($clientCity, 'UTF-8') !== mb_strtoupper('Baki', 'UTF-8')){
                        //dd(mb_strtoupper($clientCity));
                        $is_region = CourierRegion::where(function ($query) use ($clientCity) {
                            $query->whereRaw('UPPER(name_az) = UPPER(?)', [$clientCity])
                                ->orWhereRaw('UPPER(name_en) = UPPER(?)', [$clientCity]);
                        })->first();

                        if ($is_region){
                            $region_id = $is_region->id;
                        }

                        $office_amount = $filteredContracts->where('contract_id', 3)->first();

                        $amount = ($office_amount->rate * $weight) + $office_amount->charge;

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

                        //dd($azerpost_region);
                        if($azerpost_region){
                            $areaId = $azerpost_region->area_id;
                            $regionId = $azerpost_region->region_id;

                            if ($areaId){
                                $area_id = $areaId;
                                $office_amount = $filteredContracts->where('contract_id', 1)->first();

                            }else{
                                $office_amount = $filteredContracts->where('contract_id', 3)->first();

                                $region_id = $regionId;
                            }
                        }

                        $amount = ($office_amount->rate * $weight) + $office_amount->charge;
                    }


                if($region_id == null && $area_id == null){
                   // dd('test');
                    $office_amount = $filteredContracts->where('contract_id', 3)->first();
                    $amount = ($office_amount->rate * $weight) + $office_amount->charge;
                }

            }


            $merge_arr[] = array('id' => $package->id, 'amount' => $amount);

            $newData[] = [
                'id' => $package->id,
                'amount' => $amount,
            ];
        }

        /*foreach ($merge_arr as $data) {
            //dd($data);
            Package::where('id', $data['id'])->update(['partner_amount' => $data['amount']]);
        }*/

        $ids = collect($newData)->pluck('id');
        $amounts = collect($newData)->pluck('amount');

        dd($ids);
        Package::whereIn('id', $ids)->update(['partner_amount' => $amounts]);

    }

}
