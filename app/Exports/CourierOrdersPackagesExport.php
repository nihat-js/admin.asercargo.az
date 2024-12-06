<?php

namespace App\Exports;

use App\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CourierOrdersPackagesExport implements FromCollection, WithColumnFormatting, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public $from_date;
    public $to_date;

    public function __construct($from_date, $to_date)
    {
        $this->from_date = $from_date;
        $this->to_date = $to_date;
    }

    public function collection()
    {
        try {
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
                ->whereNotNull('package.courier_order_id')
                ->where('package.has_courier', 1)
                ->whereNull('package.deleted_by')
                ->whereNull('item.deleted_by')
                ->whereDate('package.has_courier_at', '>=', $this->from_date)
                ->whereDate('package.has_courier_at', '<=', $this->to_date)
                ->where('package.client_id', '<>', 121514); // sonra silinecek

            $packages = $query->orderBy('package.has_courier_at')
                ->orderBy('package.courier_order_id')
                ->select(
                'package.courier_order_id',
                'package.has_courier_at',
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
                'package.discounted_amount',
                'package.paid_status',
                'package.delivered_by',
                'status.status_en as status',
                'item.price as invoice_price',
                'invoice_currency.name as invoice_currency',
                'package.delivered_at'
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
                $courier_orders_packages_obj = new CourierOrdersPackagesObj();
                $courier_orders_packages_obj->no = $i;
                $courier_orders_packages_obj->courier_order_id = $package->courier_order_id;
                $courier_orders_packages_obj->courier_order_date = $package->has_courier_at;
                $courier_orders_packages_obj->flight = $package->flight;
                $courier_orders_packages_obj->container = $container;
                $courier_orders_packages_obj->location = $location;
                $courier_orders_packages_obj->position = $position;
                $courier_orders_packages_obj->country = $package->country;
                $courier_orders_packages_obj->suite = $package->suite;
                $courier_orders_packages_obj->client = $client;
                $courier_orders_packages_obj->track = '"' . $package->track . '"';
                $courier_orders_packages_obj->internal_id = $package->internal_id;
                $courier_orders_packages_obj->seller = $package->seller;
                $courier_orders_packages_obj->category = $package->category;
                $courier_orders_packages_obj->calculate_weight = $chargeable_weight;
                $courier_orders_packages_obj->gross_weight = $package->gross_weight;
                $courier_orders_packages_obj->volume_weight = $package->volume_weight;
                $courier_orders_packages_obj->shipping_amount = $package->shipping_amount;
                $courier_orders_packages_obj->shipping_currency = $package->shipping_currency;
                $courier_orders_packages_obj->paid = (float) ($package->paid - $package->discounted_amount);
                $courier_orders_packages_obj->discount = $package->discounted_amount;
                $courier_orders_packages_obj->paid_status = $package_paid_status;
                $courier_orders_packages_obj->invoice_price = $package->invoice_price;
                $courier_orders_packages_obj->invoice_currency = $package->invoice_currency;
                $courier_orders_packages_obj->delivered_status = $package_delivered_status;
                $courier_orders_packages_obj->status = $package->status;
                $courier_orders_packages_obj->created_date = substr($package->delivered_at, 0, 16);

                array_push($packages_arr, $courier_orders_packages_obj);
            }

            return collect($packages_arr);
        } catch (\Exception $exception) {
            $courier_orders_packages_obj = new CourierOrdersPackagesObj();
            $courier_orders_packages_obj->no = 'Something went wrong!';
            return collect(['error' => $courier_orders_packages_obj]);
        }
    }

    public function columnFormats(): array
    {
        return [
            'J' => NumberFormat::FORMAT_NUMBER
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Order No',
            'Order date',
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
            'Discount',
            'Paid status',
            'Delivered status',
            'Status',
            'Invoice price',
            'Invoice currency',
            'Delivered date'
        ];
    }
}
