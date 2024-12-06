<?php

namespace App\Exports;

use App\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PackagesExport implements FromCollection, WithHeadings, WithColumnFormatting, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    //for no invoice package (but invoice doc is not null)
    public function collection()
    {
        try {
            $query = Item::leftJoin('package', 'item.package_id', '=', 'package.id')
                ->leftJoin('currency as item_currency', 'item.currency_id', '=', 'item_currency.id')
                ->leftJoin('currency as cur', 'package.currency_id', '=', 'cur.id')
                ->leftJoin('seller as s', 'package.seller_id', '=', 's.id')
                ->leftJoin('users as c', 'package.client_id', '=', 'c.id')
                ->leftJoin('lb_status as st', 'package.last_status_id', '=', 'st.id')
                ->leftJoin('position as p', 'package.position_id', '=', 'p.id')
                ->leftJoin('countries as country', 'package.country_id', '=', 'country.id')
                ->leftJoin('locations as l', 'p.location_id', '=', 'l.id')
                ->leftJoin('locations as dep', 'package.departure_id', '=', 'dep.id')
                ->leftJoin('locations as des', 'package.destination_id', '=', 'des.id')
                ->whereNotNull('item.invoice_doc')
                ->where('package.last_status_id', 6)
                ->whereNull('package.deleted_by');

            $packages = $query->select(
                'country.name_en as country',
                'package.id',
                'package.number',
                'package.internal_id',
                'package.gross_weight',
                'package.total_charge_value',
                'cur.name as currency',
                's.name as seller',
                'item.price as invoice',
                'item_currency.name as invoice_cur',
                'package.client_id',
                'c.name as client_name',
                'c.surname as client_surname',
                'st.status_en as status',
                'item.invoice_doc',
                'l.name as location',
                'p.name as position',
                'package.container_id as container',
                'dep.name as departure',
                'des.name as destination',
                'package.created_at',
                'c.last_30_days_amount'
            )->get();

            foreach ($packages as $package) {
                $package->invoice_doc = 'https://asercargo.az/' . $package->invoice_doc;
            }

            return $packages;
        } catch (\Exception $exception) {
            $packageObj = new PackageObj();
            $packageObj->TR_NUMBER = 'Something went wrong!';
            return collect(['error' => $packageObj]);
        }
    }

    public function columnFormats(): array
    {
        return [
            'CSS' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'Track',
            'ASR',
            'Gross Weight',
            'Amount',
            'Amount currency',
            'Seller',
            'Invoice',
            'Invoice currency',
            'Suite',
            'Client name',
            'Client surname',
            'Status',
            'Invoice doc',
            'Location',
            'Position',
            'Container',
            'Departure',
            'Destination',
            'Created date',
            'Last 30 days amount'
        ];
    }
}
