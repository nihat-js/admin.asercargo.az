<?php

namespace App\Exports;

use App\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class WareHouseExport implements FromCollection, WithColumnFormatting, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public $from_date;
    public $to_date;
    public $country;
    public $flight;
    public $warehouse;
    public $status;
    public $paid_status; // yes, no
    public $access_paid;

    public function __construct($from_date, $to_date, $country, $flight, $warehouse, $status, $paid_status, $access_paid = 0)
    {
        $this->from_date = $from_date;
        $this->to_date = $to_date;
        $this->country = $country;
        $this->flight = $flight;
        $this->status = $status;
        $this->warehouse = $warehouse;
        $this->paid_status = $paid_status;
        $this->access_paid = $access_paid;
    }

    public function collection()
    {
        try {
            $from_date = $this->from_date;
            $to_date = $this->to_date;
            $country_id = $this->country;
            $flight_id = $this->flight;
            $status_id = $this->status;
            $warehouse = $this->warehouse;
            $paid_status = $this->paid_status;
            $access_paid = $this->access_paid;

            $query = Item::leftJoin('package', 'item.package_id', '=', 'package.id')
                ->leftJoin('container', 'package.last_container_id', '=', 'container.id')
                ->leftJoin('flight', 'container.flight_id', '=', 'flight.id')
                ->leftJoin('position', 'package.position_id', '=', 'position.id')
                ->leftJoin('locations', 'position.location_id', '=', 'locations.id')
                ->leftJoin('countries', 'package.country_id', '=', 'countries.id')
                ->leftJoin('users as client', 'package.client_id', '=', 'client.id')
                ->leftJoin('seller', 'package.seller_id', '=', 'seller.id')
                ->leftJoin('category', 'item.category_id', '=', 'category.id')
                ->leftJoin('lb_status as status', 'package.last_status_id', '=', 'status.id')
                ->leftJoin('tariff_types', 'package.tariff_type_id', '=', 'tariff_types.id')
                ->leftJoin('currency as shipping_currency', 'package.currency_id', '=', 'shipping_currency.id')
                ->leftJoin('currency as invoice_currency', 'item.currency_id', '=', 'invoice_currency.id')
                ->with([
                    'carrierLog' => function ($query) {
                        $query->where('carrier_status_id', 2);
                    }
                ])
                ->whereNull('package.deleted_by')
                ->whereNull('item.deleted_by')
                ->where('package.client_id', '<>', 121514) // sonra silinecek
                ->whereDate('package.created_at', '>=', $from_date)
                ->whereDate('package.created_at', '<=', $to_date);

            if (isset($country_id) && !empty($country_id) && $country_id != null) {
                $query->where('package.country_id', $country_id);
            }
            if (isset($flight_id) && !empty($flight_id) && $flight_id != null) {
                $query->where('container.flight_id', $flight_id);
            }
            if (isset($status_id) && !empty($status_id) && $status_id != null) {
                $query->where('package.last_status_id', $status_id);
            }
            if (isset($location_id) && !empty($location_id) && $location_id != null) {
                $query->whereNotNull('package.position_id');
                $query->where('position.location_id', $location_id);
            }
            if (isset($paid_status) && !empty($paid_status) && $paid_status != null && $paid_status != 'all') {
                if ($paid_status == 'yes') {
                    $query->where('package.paid_status', 1);
                } else {
                    $query->where('package.paid_status', 0);
                }
            }
            if (isset($warehouse) && !empty($warehouse) && $warehouse != null) {
                switch ($warehouse) {
                    case 'declared': {
                        $query->where('package.is_warehouse', 0);
                        $query->whereNull('package.delivered_by');
                    } break;
                    case 'external': {
                        $query->where('package.is_warehouse', 1);
                        $query->whereNull('package.delivered_by');
                    } break;
                    case 'way': {
                        $query->where('package.is_warehouse', 2);
                        $query->whereNull('package.delivered_by');
                    } break;
                    case 'internal': {
                        $query->where('package.is_warehouse', 3);
                        $query->whereNull('package.delivered_by');
                    } break;
                    case 'delivered': {
                        $query->whereNotNull('package.delivered_by');
                    } break;
                    case 'all': {
                        $query->whereIn('package.is_warehouse', [0, 1, 2, 3]);
                        $query->whereNull('package.delivered_by');
                    } break;
                }
            }

            $packages = $query->select(
                'flight.name as flight',
                'package.last_container_id as container',
                'package.position_id',
                'position.name as position',
                'locations.name as location',
                'countries.name_en as country',
                'package.client_id as suite',
                'package.client_name_surname',
                'client.name as client_name',
                'client.surname as client_surname',
		        'client.phone1 as client_phone',
                'package.number as track',
                'package.internal_id',
                'seller.name as seller',
                'category.name_en as category',
                'package.chargeable_weight',
                'package.gross_weight',
                'package.volume_weight',
                'package.total_charge_value as shipping_amount',
                'shipping_currency.name as shipping_currency',
                'package.paid',
                'package.discounted_amount',
                'package.paid_status',
                'package.delivered_by',
                'status.status_en as status',
                'item.price as invoice_price',
                'item.invoice_status as invoice_status',
                'invoice_currency.name as invoice_currency',
                'package.created_at',
                'tariff_types.name_en as tariff_type'
            )->get();

            if (count($packages) == 0) {
                $packageObj = new PackageObj();
                $packageObj->TR_NUMBER = 'Packages not found!';
                return collect(['error'=>$packageObj]);
            }

            $packages_arr = array();
            $i = 0;
            foreach ($packages as $package) {
                $invoiceStatus = 'No invoice';
                $i++;
                if ($package->position_id != null) {
                    $position = $package->position;
                    $location = $package->location;
                } else {
                    $position = '---';
                    $location = '---';
                }
                if ($package->suite == 0) {
                    $client = $package->client_name_surname;
                } else {
                    $client = $package->client_name . ' ' . $package->client_surname;
                }
                if ($package->chargeable_weight == 2) {
                    $chargeable_weight = $package->volume_weight;
                } else {
                    $chargeable_weight = $package->gross_weight;
                }
                if ($package->paid_status == 1) {
                    $package_paid_status = 'YES';
                } else {
                    $package_paid_status = 'NO';
                }
                if ($package->delivered_by == null) {
                    $package_delivered_status = 'NO';
                } else {
                    $package_delivered_status = 'YES';
                }
                if ($package->container != null) {
                    $container = 'CONTAINER' . $package->container;
                } else {
                    $container = '---';
                }
                $warehouse_obj = new WareHouseObj();
                $warehouse_obj->no = $i;
                $warehouse_obj->flight = $package->flight;
                $warehouse_obj->container = $container;
                $warehouse_obj->location = $location;
                $warehouse_obj->position = $position;
                $warehouse_obj->country = $package->country;
                $warehouse_obj->suite = $package->suite;
                $warehouse_obj->client = $client;
                $warehouse_obj->client_phone = $package->client_phone;
                $warehouse_obj->track = '"' . $package->track . '"';
                $warehouse_obj->internal_id = $package->internal_id;
                $warehouse_obj->seller = $package->seller;
                $warehouse_obj->category = $package->category;
                $warehouse_obj->tariff_type = $package->tariff_type;
                $warehouse_obj->calculate_weight = $chargeable_weight;
                $warehouse_obj->gross_weight = $package->gross_weight;
                $warehouse_obj->volume_weight = $package->volume_weight;
                if ($access_paid == 1) {
                    $warehouse_obj->shipping_amount = $package->shipping_amount;
                    $warehouse_obj->shipping_currency = $package->shipping_currency;
                    $warehouse_obj->paid = (float) ($package->paid - $package->discounted_amount);
                    $warehouse_obj->discount = $package->discounted_amount;
                    $warehouse_obj->paid_status = $package_paid_status;
                } else {
                    unset(
                        $warehouse_obj->shipping_amount,
                        $warehouse_obj->shipping_currency,
                        $warehouse_obj->paid,
                        $warehouse_obj->discount,
                        $warehouse_obj->paid_status
                    );
                }
                $warehouse_obj->delivered_status = $package_delivered_status;
                $warehouse_obj->status = $package->status;
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
                $warehouse_obj->invoice_status = $invoiceStatus;
                $warehouse_obj->invoice_price = $package->invoice_price;
                $warehouse_obj->invoice_currency = $package->invoice_currency;
                $warehouse_obj->created_date = substr($package->created_at, 0, 16);


                foreach($package->carrierLog as $declared){
                    $declared_date = $declared->created_at;
                    $warehouse_obj->declaration_date = substr($declared_date, 0, 19);
                }

                array_push($packages_arr, $warehouse_obj);
            }

            return collect($packages_arr);
        } catch (\Exception $exception) {
            //dd($exception);
            $warehouse_obj = new WareHouseObj();
            $warehouse_obj->no = 'Something went wrong!';
            return collect(['error' => $warehouse_obj]);
        }
    }

    public function columnFormats(): array
    {
        return [
            'I' => NumberFormat::FORMAT_NUMBER
        ];
    }

    public function headings(): array
    {
        if ($this->access_paid == 1) {
            return [
                'No',
                'Flight',
                'Container',
                'Location',
                'Position',
                'Country',
                'Suite',
                'Client',
                'Client Phone',
                'Track',
                'Internal ID',
                'Seller',
                'Category',
                'Tariff Type',
                'Chargeable weight',
                'Gross weight',
                'Volume weight',
                'Shipping amount',
                'Shipping currency',
                'Paid',
                'Discount',
                'Paid status',
                'Delivered status',
                'Status',
                'Invoice status',
                'Invoice price',
                'Invoice currency',
                'Date',
                'Declared Date'
            ];
        }

        return [
            'No',
            'Flight',
            'Container',
            'Location',
            'Position',
            'Country',
            'Suite',
            'Client',
            'Client Phone',
            'Track',
            'Internal ID',
            'Seller',
            'Category',
            'Tariff Type',
            'Chargeable weight',
            'Gross weight',
            'Volume weight',
            'Delivered status',
            'Status',
            'Invoice status',
            'Invoice price',
            'Invoice currency',
            'Date',
            'Declared Date'
        ];
    }
}
