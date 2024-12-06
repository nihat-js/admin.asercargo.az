<?php

namespace App\Exports;

use App\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class InBakuExport implements FromCollection, WithColumnFormatting, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public $access_paid;

    public function __construct($access_paid = 0)
    {
        $this->access_paid = $access_paid;
    }

    public function collection()
    {
        try {
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
                ->leftJoin('currency as shipping_currency', 'package.currency_id', '=', 'shipping_currency.id')
                ->leftJoin('currency as invoice_currency', 'item.currency_id', '=', 'invoice_currency.id')
                ->whereNull('package.deleted_by')
                ->whereNull('item.deleted_by')
                ->where('package.client_id', '<>', 121514); // sonra silinecek

            $query->whereRaw('(package.is_warehouse = 3 or package.customs_date is not null)');
            $query->where('package.in_baku', 1);
            $query->whereNull('package.delivered_by');

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
                'package.paid_status',
                'package.delivered_by',
                'status.status_en as status',
                'item.price as invoice_price',
                'invoice_currency.name as invoice_currency',
                'package.created_at'
            )
                ->get();

            if (count($packages) == 0) {
                $packageObj = new PackageObj();
                $packageObj->TR_NUMBER = 'Packages not found!';
                return collect(['error'=>$packageObj]);
            }

            $packages_arr = array();
            $i = 0;
            foreach ($packages as $package) {
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
                $warehouse_obj->track = '"' . $package->track . '"';
                $warehouse_obj->internal_id = $package->internal_id;
                $warehouse_obj->seller = $package->seller;
                $warehouse_obj->category = $package->category;
                $warehouse_obj->calculate_weight = $chargeable_weight;
                $warehouse_obj->gross_weight = $package->gross_weight;
                $warehouse_obj->volume_weight = $package->volume_weight;
                if ($access_paid == 1) {
                    $warehouse_obj->shipping_amount = $package->shipping_amount;
                    $warehouse_obj->shipping_currency = $package->shipping_currency;
                    $warehouse_obj->paid = $package->paid;
                    $warehouse_obj->paid_status = $package_paid_status;

                    $warehouse_obj->invoice_price = $package->invoice_price;
                    $warehouse_obj->invoice_currency = $package->invoice_currency;
                } else {
                    unset(
                        $warehouse_obj->shipping_amount,
                        $warehouse_obj->shipping_currency,
                        $warehouse_obj->paid,
                        $warehouse_obj->paid_status,
                        $warehouse_obj->invoice_price,
                        $warehouse_obj->invoice_currency
                    );
                }
                $warehouse_obj->delivered_status = $package_delivered_status;
                $warehouse_obj->status = $package->status;

                $warehouse_obj->created_date = substr($package->created_at, 0, 16);

                array_push($packages_arr, $warehouse_obj);
            }

            return collect($packages_arr);
        } catch (\Exception $exception) {
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
                'Track',
                'Internal ID',
                'Seller',
                'Category',
                'Chargeable weight',
                'Gross weight',
                'Volume weight',
                'Shipping amount',
                'Shipping currency',
                'Paid',
                'Paid status',
                'Invoice price',
                'Invoice currency',
                'Delivered status',
                'Status',
                'Created date'
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
            'Track',
            'Internal ID',
            'Seller',
            'Category',
            'Chargeable weight',
            'Gross weight',
            'Volume weight',
            'Delivered status',
            'Status',
            'Created date'
        ];
    }
}
