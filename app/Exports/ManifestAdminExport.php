<?php

namespace App\Exports;

use App\Container;
use App\Flight;
use App\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ManifestAdminExport implements FromCollection, WithColumnFormatting, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public  $flight_id;

    public function __construct($flight_id)
    {
        $this->flight_id = $flight_id;
    }

    public function collection()
    {
        try {
            $flight_id = $this->flight_id;

            $flight = Flight::where('id', $flight_id)->whereNull('deleted_by')
                ->select('name')
                ->first();

            if (!$flight) {
                $packageObj = new PackageObj();
                $packageObj->TR_NUMBER = 'Flight not found!';
                return collect(['error'=>$packageObj]);
            }

            $flight_name = $flight->name;

            $containers = Container::where('flight_id', $flight_id)->whereNull('deleted_by')
                ->select('id')->get();

            if (count($containers) == 0) {
                $packageObj = new PackageObj();
                $packageObj->TR_NUMBER = 'Container not found!';
                return collect(['error'=>$packageObj]);
            }

            $containers_arr = array();
            foreach ($containers as $container) {
                array_push($containers_arr, $container->id);
            }

            $packages = Item::leftJoin('package as p', 'item.package_id', '=', 'p.id')
                ->leftJoin('users as c', 'p.client_id', '=', 'c.id')
                ->leftJoin('lb_status as status', 'p.last_status_id', '=', 'status.id')
                ->leftJoin('category as cat', 'item.category_id', '=', 'cat.id')
                ->leftJoin('seller as s', 'p.seller_id', '=', 's.id')
                ->leftJoin('position', 'p.position_id', '=', 'position.id')
                ->leftJoin('locations', 'position.location_id', '=', 'locations.id')
                ->leftJoin('tariff_types', 'p.tariff_type_id', '=', 'tariff_types.id')
                ->leftJoin('currency as amount_currency', 'p.currency_id', '=', 'amount_currency.id')
                ->leftJoin('currency as invoice_currency', 'item.currency_id', '=', 'invoice_currency.id')
                ->whereIn('p.last_container_id', $containers_arr)
                ->whereNUll('item.deleted_by')
                ->whereNUll('p.deleted_by')
                ->select(
                    'p.number as track',
                    'p.internal_id',
                    'p.gross_weight',
                    'p.volume_weight',
                    'p.chargeable_weight',
                    'p.total_charge_value as amount',
                    'invoice_status',
                    'amount_currency.name as amount_currency',
                    'cat.name_en as category',
                    's.name as seller',
                    'p.client_id as suite',
                    'c.name as client_name',
                    'c.surname as client_surname',
                    'c.passport_number as client_passport',
                    'c.passport_fin as client_fin',
                    'c.phone1 as client_phone',
                    'c.email as client_email',
                    'item.price as invoice',
                    'invoice_currency.name as invoice_currency',
                    'p.last_container_id as container',
                    'p.position_id',
                    'locations.name as location',
                    'position.name as position',
                    'status.status_en as status',
                    'p.collected_at as date',
                    'c.last_30_days_amount',
                    'p.other_seller',
                    'item.title as title',
                    'c.address1 as client_address',
                    'tariff_types.name_en as tariff_type'
                )
                ->get();

            if (count($packages) == 0) {
                $packageObj = new PackageObj();
                $packageObj->TR_NUMBER = 'Packages not found!';
                return collect(['error'=>$packageObj]);
            }

            $packages_arr = array();

            foreach ($packages as $package) {
                $manifest = new ManifestObj();
                $seller = $package->other_seller == null ? $package->seller : $package->other_seller;
                $manifest->flight = $flight_name;
                $invoiceStatus = 'No invoice';
                if ($package->container != null) {
                    $container = 'CONTAINER' . $package->container;
                } else {
                    $container = '---';
                }
                $manifest->container = $container;
                if ($package->position_id != null) {
                    $position = $package->position;
                    $location = $package->location;
                } else {
                    $position = '---';
                    $location = '---';
                }
                $manifest->location = $location;
                $manifest->position = $position;
                $manifest->track = '"' . $package->track . '"';
                $manifest->internal_id = $package->internal_id;
                $manifest->gross_weight = $package->gross_weight;
                $manifest->volume_weight = $package->volume_weight;
                if ($package->chargeable_weight == 2) {
                    $chargeable_weight = $package->volume_weight;
                } else {
                    $chargeable_weight = $package->gross_weight;
                }
                $manifest->chargeable_weight = $chargeable_weight;
                $manifest->amount = $package->amount;
                $manifest->amount_currency = $package->amount_currency;
                $manifest->category = $package->category;
                $manifest->tariff_type = $package->tariff_type;
                $manifest->seller = $seller;
                $manifest->suite = $package->suite;
                $manifest->client = $package->client_name . ' ' . $package->client_surname;
                $manifest->client_passport = $package->client_passport;
                $manifest->client_fin = $package->client_fin;
                $manifest->client_phone = $package->client_phone;
                $manifest->client_email = $package->client_email;
                $manifest->client_address = $package->client_address;
                $manifest->invoice = $package->invoice;
                $manifest->invoice_currency = $package->invoice_currency;
                $manifest->status = $package->status;
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
                $manifest->invoice_status = $invoiceStatus;
                $manifest->client_last_30_days_amount = $package->last_30_days_amount;

                $manifest->date = substr($package->date, 0, 16);
                $manifest->title = $package->title;

                array_push($packages_arr, $manifest);
            }

            return collect($packages_arr);
        } catch (\Exception $e) {
            //dd($e);
            $packageObj = new PackageObj();
            $packageObj->TR_NUMBER = 'Something went wrong!';
            return collect(['error'=>$packageObj]);
        }
    }

    public function columnFormats(): array
    {
        return [
            'P' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'R' => NumberFormat::FORMAT_NUMBER
        ];
    }

    public function headings(): array
    {
        return [
            'Flight',
            'Container',
            'Location',
            'Position',
            'Track',
            'Internal ID',
            'Gross weight',
            'Volume weight',
            'Chargeable weight',
            'Amount',
            'Amount currency',
            'Category',
            'Tariff Type',
            'Seller',
            'Suite',
            'Client',
            'Client passport',
            'Client FIN',
            'Client phone',
            'Client email',
            'Client address',
            'Invoice price',
            'Invoice currency',
            'Status',
            'Invoice status',
            'Last 30 days amount',
            'Date',
            'Title'
        ];
    }
}
