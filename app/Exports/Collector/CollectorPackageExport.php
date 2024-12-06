<?php

namespace App\Exports\Collector;

use App\CustomCategory;
use App\Item;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CollectorPackageExport implements FromCollection, WithColumnFormatting, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public $type;
    public $flight_id;
    public $from_date;
    public $to_date;
    public $request;

    public function __construct($type, $flight_id = null, $from_date = null, $to_date = null, $request)
    {
        $this->type = $type;
        $this->flight_id = $flight_id;
        $this->from_date = $from_date;
        $this->to_date = $to_date;
        $this->request = $request;
    }

    public function collection()
    {
        try {

            $user_id = Auth::user() != null ? Auth::user()->id : $this->request->collector->id;
            $user_location = Auth::user() != null ? Auth::user()->location() : $this->request->collector->location();

            $query = Item::LeftJoin('package', 'item.package_id', '=', 'package.id')
                ->leftJoin('users as client', 'package.client_id', '=', 'client.id')
                ->leftJoin('currency as cur', 'item.currency_id', '=', 'cur.id')
                ->leftJoin('seller as s', 'package.seller_id', '=', 's.id')
                ->leftJoin('category as cat', 'item.category_id', '=', 'cat.id')
                ->leftJoin('position as pos', 'package.position_id', '=', 'pos.id')
                ->leftJoin('container', 'package.last_container_id', '=', 'container.id')
                ->leftJoin('lb_status as status', 'package.last_status_id', '=', 'status.id')
                ->leftJoin('custom_category', 'item.custom_cat_id', '=', 'custom_category.id')
                ->whereNull('item.deleted_by')
                ->with([
                    'carrierLog' => function ($query) {
                        $query->where('carrier_status_id', 2);
                    }
                ])
                ->whereNull('package.deleted_by');

            switch ($this->type) {
                case 'manifest':
                    {
                        $query->where('container.flight_id', $this->flight_id);
                    }
                    break;
                case 'no_invoice':
                    {
                        if($user_id == 137297){
                            $query->where('package.departure_id', $user_location)
                                ->whereDate('package.collected_at', '>=', $this->from_date)
                                ->whereDate('package.collected_at', '<=', $this->to_date)
                                ->whereDate('package.created_at', '>', date('2021-08-20'));
                        }else{
                            $query->where('package.departure_id', $user_location)
                            ->whereDate('package.collected_at', '>=', $this->from_date)
                            ->whereDate('package.collected_at', '<=', $this->to_date);
                        }
                    }
                    break;
                case 'incorrect_invoice':
                    {
                        if($user_id == 137297){
                            $query->where('package.departure_id', $user_location)
                                ->where('package.last_status_id', 9)
                                ->whereDate('package.collected_at', '>=', $this->from_date)
                                ->whereDate('package.collected_at', '<=', $this->to_date)
                                ->whereDate('package.created_at', '>', date('2021-08-20'));
                        }else{
                            $query->where('package.departure_id', $user_location)
                            ->where('package.last_status_id', 9)
                            ->whereDate('package.collected_at', '>=', $this->from_date)
                            ->whereDate('package.collected_at', '<=', $this->to_date);
                        }
                    }
                    break;
                case 'prohibited':
                    {
                        if($user_id == 137297){
                            $query->where('package.departure_id', $user_location)
                                ->where('package.last_status_id', 7)
                                ->whereDate('package.collected_at', '>=', $this->from_date)
                                ->whereDate('package.collected_at', '<=', $this->to_date)
                                ->whereDate('package.created_at', '>', date('2021-08-20'));
                        }else{
                            $query->where('package.departure_id', $user_location)
                            ->where('package.last_status_id', 7)
                            ->whereDate('package.collected_at', '>=', $this->from_date)
                            ->whereDate('package.collected_at', '<=', $this->to_date);
                        }
                    }
                    break;
                case 'damaged':
                    {
                        if($user_id == 137297){
                            $query->where('package.departure_id', $user_location)
                                ->where('package.last_status_id', 8)
                                ->whereDate('package.collected_at', '>=', $this->from_date)
                                ->whereDate('package.collected_at', '<=', $this->to_date)
                                ->whereDate('package.created_at', '>', date('2021-08-20'));
                        }else{
                            $query->where('package.departure_id', $user_location)
                            ->where('package.last_status_id', 8)
                            ->whereDate('package.collected_at', '>=', $this->from_date)
                            ->whereDate('package.collected_at', '<=', $this->to_date);
                        }
                    }
                    break;
                case 'all_packages':
                    {
                        if($user_id == 137297){
                            $query->where('package.departure_id', $user_location)
                                ->whereNotIn('package.last_status_id', [3, 15, 29, 30, 33, 34])
                                ->whereDate('package.collected_at', '>=', $this->from_date)
                                ->whereDate('package.collected_at', '<=', $this->to_date)
                                ->whereDate('package.created_at', '>', date('2021-08-20'));
                        }else{
                            $query->where('package.departure_id', $user_location)
                            ->whereNotIn('package.last_status_id', [3, 15, 29, 30, 33, 34])
                            ->whereDate('package.collected_at', '>=', $this->from_date)
                            ->whereDate('package.collected_at', '<=', $this->to_date);
                        }
                    }
                    break;
                default:
                {
                    $packageObj = new CollectorPackageObj();
                    $packageObj->no = 'Wrong type!';
                    return collect(['error' => $packageObj]);
                }
            }

            $packages = $query->orderBy('package.id')
                ->select(
                    'package.client_id',
                    'client.name as client_name',
                    'client.surname as client_surname',
                    'package.number',
                    'package.internal_id',
                    'status.status_en as status',
                    'package.container_id',
                    'package.position_id',
                    'pos.name as position',
                    'package.gross_weight',
                    's.name as seller',
                    'cat.name_en as category',
                    'item.price',
                    'cur.name as currency',
                    'item.invoice_doc',
                    'item.invoice_uploaded_date',
                    'item.invoice_status as invoice_status',
                    'item.custom_cat_id',
                    'item.subCat',
                    'custom_category.parentId',
                    'custom_category.goodsNameEn'
                )
                ->get();


            if (count($packages) == 0) {
                $packageObj = new CollectorPackageObj();
                $packageObj->no = 'Packages not found!';
                return collect(['error' => $packageObj]);
            }

            $packages_arr = array();
            $no = 0;

            foreach ($packages as $package) {
                $invoiceStatus = 'No invoice';
                $packageObj = new CollectorPackageObj();

                $no++;

                if ($package->container_id != null) {
                    $storage = 'CONTAINER' . $package->container_id;
                } else if ($package->position_id != null) {
                    $storage = $package->position;
                } else {
                    $storage = '---';
                }

                if ($package->invoice_doc == null) {
                    $invoice_file_exists = 'NO';
                } else {
                    $invoice_file_exists = 'YES';
                }

                $packageObj->no = $no;
                $packageObj->suite = $package->client_id;
                $packageObj->client = $package->client_name . ' ' . $package->client_surname;
                $packageObj->internal_id = $package->internal_id;
                $packageObj->track = '"' . $package->number . '"';
                $packageObj->status = $package->status;
                $packageObj->storage = $storage;
                $packageObj->weight = $package->gross_weight;
                $packageObj->category = $package->category;
                $packageObj->seller = $package->seller;
                $packageObj->invoice_price = $package->price;
                $packageObj->currency = $package->currency;
                $packageObj->invoice_file_exists = $invoice_file_exists;
                $packageObj->invoice_uploaded_date = $package->invoice_uploaded_date;
                switch ($package->invoice_status) {
                    case 1: {
                        $invoiceStatus = 'No invoice';
                        break;
                    }
                    case 2: {
                        $invoiceStatus = 'Incorrect invoice';
                        break;
                    }
                    case 3: {
                        $invoiceStatus = 'Invoice available';
                        break;
                    }
                    case 4: {
                        $invoiceStatus = 'Invoice uploaded';
                        break;
                    }
                }
                $packageObj->invoice_status = $invoiceStatus;
                $packageObj->custom_category = $package->subCat;

                if($package->subCat == null){
                    $packageObj->custom_category = '---';
                }else{
                    $packageObj->custom_category = $package->subCat;
                }

                foreach($package->carrierLog as $declared){
                    $declared_date = $declared->created_at;
                    $packageObj->declaration_date = substr($declared_date, 0, 19);
                }

                // dd($packageObj);
                // $stringToArray = array_map('intval', explode(",", $package->custom_cat_id));
                // $customID = CustomCategory::whereIn('id', $stringToArray)
                //     ->select('id', 'goodsNameEn', 'parentId')
                //     ->get();

                // $packages_arr_for_update = '';
                // foreach ($customID as $ids) {

                //     if($ids->goodsNameEn == 'Others'){
                //           $customIDs = CustomCategory::where('parentId', 0)->where('id', $ids->parentId)->select('id', 'goodsNameEn')->first();
                //         $packages_arr_for_update .= $ids->goodsNameEn . '-' . $customIDs->goodsNameEn . ',';
                //     }else{
                //         $packages_arr_for_update .= $ids->goodsNameEn . ',';
                //     }
                // }

                // $packages_arr_for_update = substr($packages_arr_for_update, 0, -1);
                // if(Auth::user()->location() == 6){
                    // $packageObj->custom_category = $package->subCat;
                // }
                
                array_push($packages_arr, $packageObj);
            }

            return collect($packages_arr);
        } catch (\Exception $e) {
            //dd($e);
            $packageObj = new CollectorPackageObj();
            $packageObj->no = 'Something went wrong!';
            return collect(['error' => $packageObj]);
        }
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Client ID',
            'Client',
            'ASR number',
            'Tracking number',
            'Status',
            'Storage',
            'Weight',
            'Category',
            'Seller',
            'Invoice price',
            'Currency',
            'Invoice file exists',
            'Invoice uploaded date',
            'Invoice status',
            'Custom Category',
            'Declaration Date'
        ];
    }
}
