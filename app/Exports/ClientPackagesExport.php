<?php

namespace App\Exports;

use App\Container;
use App\Flight;
use App\Item;
use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ClientPackagesExport implements FromCollection, WithColumnFormatting, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public  $client_id;
    public  $has_delivered;
    public  $has_referrals;

    public function __construct($client_id, $has_delivered, $has_referrals)
    {
        $this->client_id = $client_id;
        $this->has_delivered = $has_delivered;
        $this->has_referrals = $has_referrals;
    }

    public function collection()
    {
        try {
            $users = array();
            array_push($users, $this->client_id);

            if ($this->has_referrals == 'yes') {
                $sub_accounts = User::where('parent_id', $this->client_id)
                    ->whereNull('deleted_by')
                    ->select('id')
                    ->get();

                foreach ($sub_accounts as $sub_account) {
                    array_push($users, $sub_account->id);
                }
            }

            $query = Item::leftJoin('package as p', 'item.package_id', '=', 'p.id')
                ->leftJoin('users as c', 'p.client_id', '=', 'c.id')
                ->leftJoin('lb_status as status', 'p.last_status_id', '=', 'status.id')
                ->leftJoin('seller as s', 'p.seller_id', '=', 's.id')
                ->leftJoin('currency as amount_currency', 'p.currency_id', '=', 'amount_currency.id')
                ->whereIn('p.client_id', $users)
                ->whereNUll('item.deleted_by')
                ->whereNUll('p.deleted_by');

           if ($this->has_delivered != 'yes') {
               $query->whereNUll('p.delivered_by');
           }

            $packages = $query->select(
                'p.number as track',
                'p.internal_id',
                'p.gross_weight',
                'p.total_charge_value as amount',
                'amount_currency.name as amount_currency',
                's.name as seller',
                'p.client_id as suite',
                'c.name as client_name',
                'c.surname as client_surname',
                'status.status_en as status'
            )->get();

            if (count($packages) == 0) {
                $packageObj = new ClientPackagesObj();
                $packageObj->no = 'Packages not found!';
                return collect(['error'=>$packageObj]);
            }

            $packages_arr = array();

            $no = 0;
            foreach ($packages as $package) {
                $packageObj = new ClientPackagesObj();

                $no++;

                $packageObj->no = $no;
                $packageObj->client_id = 'AS' . $package->suite;
                $packageObj->client = $package->client_name . ' ' . $package->client_surname;
                $packageObj->track = '"' . $package->track . '"';
                $packageObj->internal_id = $package->internal_id;
                $packageObj->weight = $package->gross_weight;
                $packageObj->amount = $package->amount;
                $packageObj->currency = $package->amount_currency;
                $packageObj->seller = $package->seller;
                $packageObj->status = $package->status;

                array_push($packages_arr, $packageObj);
            }

            return collect($packages_arr);
        } catch (\Exception $e) {
            $packageObj = new PackageObj();
            $packageObj->TR_NUMBER = 'Something went wrong!';
            return collect(['error'=>$packageObj]);
        }
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Client ID',
            'Client',
            'Track',
            'ASR',
            'Weight',
            'Amount',
            'Currency',
            'Seller',
            'Status'
        ];
    }
}
