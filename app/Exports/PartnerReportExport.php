<?php

namespace App\Exports;

use App\CustomCategory;
use App\Item;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PartnerReportExport implements FromCollection, WithColumnFormatting, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

   
    public $from_date;
    public $to_date;

    public function __construct($from_date = null, $to_date = null)
    {
  
        $this->from_date = $from_date;
        $this->to_date = $to_date;
    }

    public function collection()
    {
   
        try {
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
                ->whereNull('package.deleted_by');

                $query->where('seller_id', 1338)
                    ->whereDate('package.collected_at', '>=', $this->from_date)
                    ->whereDate('package.collected_at', '<=', $this->to_date);

            $packages = $query->orderBy('package.id')
                ->select(
                    'package.client_id',
                    'package.seller_id',
                    'client.name as client_name',
                    'client.surname as client_surname',
                    'package.number',
                    'package.internal_id',
                    'status.status_en as status',
                    'package.container_id',
                    'package.position_id',
                    'pos.name as position',
                    'package.gross_weight',
                    'package.total_charge_value',
                    'package.amount_usd',
                    'package.collected_at',
                    'package.created_at',
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
                $packageObj = new PartnerObj();
                $packageObj->no = 'Packages not found!';
                return collect(['error' => $packageObj]);
            }

            $packages_arr = array();
            $no = 0;

            foreach ($packages as $package) {
                // dd($package);
                $invoiceStatus = 'No invoice';
                $packageObj = new PartnerObj();

                $no++;

                if ($package->container_id != null) {
                    $storage = 'CONTAINER' . $package->container_id;
                } else if ($package->position_id != null) {
                    $storage = $package->position;
                } else {
                    $storage = '---';
                }

                // if ($package->invoice_doc == null) {
                //     $invoice_file_exists = 'NO';
                // } else {
                //     $invoice_file_exists = 'YES';
                // }

                $packageObj->no = $no;
                $packageObj->suite = $package->client_id;
                $packageObj->client = $package->client_name . ' ' . $package->client_surname;
                $packageObj->internal_id = $package->internal_id;
                $packageObj->track = '"' . $package->number . '"';
                $packageObj->storage = $storage;
                $packageObj->status = $package->status;
                $packageObj->weight = $package->gross_weight;
                $packageObj->transport_cost = $package->total_charge_value;
                $packageObj->transport_cost_usd = $package->amount_usd;
                $packageObj->category = $package->category;
                $packageObj->seller = $package->seller;
                $packageObj->invoice_price = $package->price;
                $packageObj->currency = $package->currency;
                $packageObj->collect = $package->collected_at;
                $packageObj->created = $package->created_at;

          
                
                array_push($packages_arr, $packageObj);
            }

            return collect($packages_arr);
        } catch (\Exception $e) {
            // dd($e);
            $packageObj = new PartnerObj();
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
            'Storage',
            'Status',
            'Weight',
            'Transport Cost',
            'Transport Cost USD',
            'Category',
            'Seller',
            'Invoice price',
            'Currency',
            'Collected_at',
            'Created_at'
        ];
    }
}
