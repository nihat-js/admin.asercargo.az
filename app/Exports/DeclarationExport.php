<?php

namespace App\Exports;

use App\Container;
use App\ExchangeRate;
use App\Flight;
use App\Item;
use App\Location;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DeclarationExport implements FromCollection, WithColumnFormatting, WithHeadings, ShouldAutoSize
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

            $flight = Flight::leftJoin('locations as l', 'flight.location_id', '=', 'l.id')
                ->where('flight.id', $flight_id)->whereNull('flight.deleted_by')
                ->select(
                    'flight.location_id',
                    'l.currency_type',
                    'l.goods_fr',
                    'l.goods_to',
                    'l.address'
                )
                ->first();

            if (!$flight) {
                $packageObj = new PackageObj();
                $packageObj->TR_NUMBER = 'Flight not found!';
                return collect(['error'=>$packageObj]);
            }

            $containers = Container::where('flight_id', $flight_id)->whereNull('deleted_by')
                ->select('id')->get();

            $packages = Item::leftJoin('package as p', 'item.package_id', '=', 'p.id')
                ->leftJoin('users as c', 'p.client_id', '=', 'c.id')
                ->leftJoin('category as cat', 'item.category_id', '=', 'cat.id')
                ->leftJoin('seller as s', 'p.seller_id', '=', 's.id')
                ->whereIn('p.last_container_id', $containers)
                ->where('p.client_id', '<>', 0)
                ->whereNUll('item.deleted_by')
                ->whereNUll('p.deleted_by')
                ->whereNUll('c.deleted_by')
                ->select(
                    'item.id as item_id',
                    'p.internal_id as cbr',
                    'p.gross_weight',
                    'p.container_id',
                    'cat.name_en as category',
                    'p.client_id as suite',
                    'c.name as client_name',
                    'c.surname as client_surname',
                    'c.address1 as client_address',
                    's.name as seller',
                    'p.number as track',
                    'c.passport_fin as client_fin',
                    'c.phone1 as client_phone',
                    'item.price as invoice',
                    'item.currency_id as invoice_currency',
                    'p.total_charge_value as amount',
                    'p.currency_id as amount_currency',
                    'c.last_30_days_amount'
                )
                ->get();

            if (count($packages) == 0) {
                $packageObj = new PackageObj();
                $packageObj->TR_NUMBER = 'Packages not found!';
                return collect(['error'=>$packageObj]);
            }

            $country = Location::leftJoin('countries as c', 'locations.country_id', '=', 'c.id')
                ->where('locations.id', $flight->location_id)
                ->select('c.currency_id', 'c.currency_for_declaration')
                ->first();

            if (!$country) {
                $packageObj = new PackageObj();
                $packageObj->TR_NUMBER = 'Country not found!';
                return collect(['error'=>$packageObj]);
            }

            $location_currency = $country->currency_id;
            $currency_for_declaration = $country->currency_for_declaration;

            $date = Carbon::today();
            $rates = ExchangeRate::whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
                ->select('rate', 'from_currency_id', 'to_currency_id')
                ->get();
            if (!$rates) {
                $packageObj = new PackageObj();
                $packageObj->TR_NUMBER = 'Rates not found!';
                return collect(['error'=>$packageObj]);
            }

            $i = 0;
            $packages_arr = array();

            foreach ($packages as $package) {
                $rate_for_shipping_price = $this->calculate_exchange_rate($rates, $package->amount_currency, $currency_for_declaration);
                $shipping_price = $package->amount * $rate_for_shipping_price;
                $shipping_price_for_total = $shipping_price;
                $shipping_price = sprintf('%0.2f', $shipping_price);

                if ($rate_for_shipping_price == 0) {
//                    $packageObj = new PackageObj();
//                    $packageObj->TR_NUMBER = 'Rate not found: rate_for_shipping_price (' . $package->amount_currency . ' -> ' . $location_currency . ')!';
//                    return collect(['error'=>$packageObj]);
                    $shipping_price = 'Rate not found: rate invoice price not found for item: ' . $package->item_id . ', ASR: ' . $package->cbr;
                }

                $rate_for_invoice_price = $this->calculate_exchange_rate($rates, $package->invoice_currency, $currency_for_declaration);
                $invoice_price = $package->invoice * $rate_for_invoice_price;
                $invoice_price_for_total = $invoice_price;
                $invoice_price = sprintf('%0.2f', $invoice_price);
                if ($rate_for_invoice_price == 0) {
//                    $packageObj = new PackageObj();
//                    $packageObj->TR_NUMBER = 'Rate not found: rate_for_invoice_price (' . $package->invoice_currency . ' -> ' . $currency_for_declaration . ')!' . ' for item id ' . $package->cbr;
//                    return collect(['error'=>$packageObj]);
                    $invoice_price = 'Rate not found: rate invoice price not found for item: ' . $package->item_id . ', ASR: ' . $package->cbr;
                }
                $total_price = floatval($shipping_price_for_total) + floatval($invoice_price_for_total);
                $total_price = sprintf('%0.2f', $total_price);
                if ($package->container_id != null) {
                    $storage = 'CONTAINER' . $package->container_id;
                } else if ($package->position_id != null) {
                    $storage = $package->position;
                } else {
                    $storage = '---';
                }

                $i++;

                $packageObj = new PackageObj();
                $packageObj->TR_NUMBER = $package->cbr;
                $packageObj->STORAGE = $storage;
                $packageObj->DIRECTION = 1;
                $packageObj->QUANTITY_OF_GOODS = 1;
                $packageObj->WEIGHT_GOODS = $package->gross_weight;
                $packageObj->INVOYS_PRICE = $total_price;
                $packageObj->CURRENCY_TYPE = $flight->currency_type;
                $packageObj->NAME_OF_GOODS = $package->category;
                $packageObj->SUIT = $package->suite;
                $packageObj->IDXAL_NAME = $package->client_name . ' ' . $package->client_surname;
                $packageObj->IDXAL_ADRESS = $package->client_address;
                $packageObj->IXRAC_NAME = $package->seller;
                $packageObj->IXRAC_ADRESS = $flight->address;
                $packageObj->GOODS_TRAFFIC_FR = $flight->goods_fr;
                $packageObj->GOODS_TRAFFIC_TO = $flight->goods_to;
                $packageObj->QAIME = $package->cbr;
                $packageObj->TRACKING_NO = $package->track;
                $packageObj->FIN = $package->client_fin;
                $packageObj->invoice_price = sprintf('%0.2f', $invoice_price_for_total);
                $packageObj->Shipping_Price = $shipping_price;
                $packageObj->client_last_30_days_amount = $package->last_30_days_amount;
                if(Auth::user()->role() != 9){
                    $packageObj->PHONE = $package->client_phone;
                }

                array_push($packages_arr, $packageObj);
            }

            return collect($packages_arr);
        } catch (\Exception $e) {
            dd($e);
            $packageObj = new PackageObj();
            $packageObj->TR_NUMBER = 'Something went wrong!';
            return collect(['error'=>$packageObj]);
        }
    }

    private function calculate_exchange_rate($rates, $from, $to)
    {
        try {
            if ($from == $to) {
                return 1;
            }

            foreach ($rates as $rate) {
                if ($rate->from_currency_id == $from && $rate->to_currency_id == $to) {
                    return $rate->rate;
                }
            }

            return 0;
        } catch (\Exception $exception) {
            return 0;
        }
    }

    public function columnFormats(): array
    {
        return [
            'O' => NumberFormat::FORMAT_NUMBER,
            'Q' => NumberFormat::FORMAT_NUMBER
        ];
    }

    public function headings(): array
    {
        if(Auth::user()->role() !=9){
            $phone = 'PHONE';
        }else{
            $phone = '';
        }
        return [
            'TR_NUMBER',
            'CONTAINER',
            'DIRECTION',
            'QUANTITY_OF_GOODS',
            'WEIGHT_GOODS',
            'INVOYS_PRICE',
            'CURRENCY_TYPE',
            'NAME_OF_GOODS',
            'SUIT',
            'IDXAL_NAME',
            'IDXAL_ADRESS',
            'IXRAC_NAME',
            'IXRAC_ADRESS',
            'GOODS_TRAFFIC_FR',
            'GOODS_TRAFFIC_TO',
            'QAIME',
            'TRACKING NO',
            'FIN',
            'İnvoice price',
            'Shipping Price',
            'Last 30 days amount',
            $phone,
        ];
    }
}
