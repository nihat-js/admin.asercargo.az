<?php

namespace App\Exports;

use App\CashierLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CashierReportExport implements FromCollection, WithHeadings, ShouldAutoSize, WithColumnFormatting
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

    public function collection()
    {
        try {
            $from_date = $this->from_date;
            $to_date = $this->to_date;

            $logs = CashierLog::leftJoin('users as cashier', 'cashier_log.created_by', '=', 'cashier.id')
                ->leftJoin('users as client', 'cashier_log.client_id', '=', 'client.id')
                ->whereDate('cashier_log.created_at', '>=', $from_date)
                ->whereDate('cashier_log.created_at', '<=', $to_date)
                ->select(
                    'cashier_log.client_id as suite',
                    'client.name as client_name',
                    'client.surname as client_surname',
                    'cashier_log.payment_azn',
                    'cashier_log.payment_usd',
                    'cashier_log.added_to_balance',
                    'cashier_log.old_balance',
                    'cashier_log.new_balance',
                    'cashier_log.receipt',
                    'cashier.name as cashier_name',
                    'cashier.surname as cashier_surname',
                    'cashier_log.type',
                    'cashier_log.created_at'
                )
                ->get();

            $logs_arr = array();
            $i = 0;
            foreach ($logs as $log) {
                $i++;
                $cashier_log_obj = new CashierLogObj();
                $cashier_log_obj->no = $i;
                $cashier_log_obj->suite = $log->suite;
                $cashier_log_obj->client = $log->client_name . ' ' . $log->client_surname;
                $cashier_log_obj->payment_azn = $log->payment_azn;
                $cashier_log_obj->payment_usd = $log->payment_usd;
                $cashier_log_obj->added_to_balance = $log->added_to_balance;
                $cashier_log_obj->old_balance = $log->old_balance;
                $cashier_log_obj->new_balance = $log->new_balance;
                $cashier_log_obj->type = $log->type;
                $cashier_log_obj->receipt = $log->receipt;
                $cashier_log_obj->cashier = $log->cashier_name . ' ' . $log->cashier_surname;
                $cashier_log_obj->created_date = substr($log->created_at, 0, 16);

                array_push($logs_arr, $cashier_log_obj);
            }

            return collect($logs_arr);
        } catch (\Exception $e) {
            $cashier_log_obj = new CashierLogObj();
            $cashier_log_obj->no = 'Something went wrong!';
            return collect(['error'=>$cashier_log_obj]);
        }
    }

    public function columnFormats(): array
    {
        return [
            'L' => NumberFormat::FORMAT_DATE_DATETIME
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Suite',
            'Client',
            'Payment (AZN)',
            'Payment (USD)',
            'Added to balance (AZN)',
            'Old balance (USD)',
            'New balance (USD)',
            'Type',
            'Receipt',
            'Cashier',
            'Date'
        ];
    }
}
