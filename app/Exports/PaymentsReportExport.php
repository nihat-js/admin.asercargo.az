<?php

namespace App\Exports;

use App\ExchangeRate;
use App\PaymentLog;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PaymentsReportExport implements FromCollection, WithHeadings, ShouldAutoSize, WithColumnFormatting
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

            $date = Carbon::today();
            $rates = ExchangeRate::whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
                ->select('rate', 'from_currency_id', 'to_currency_id')
                ->get();
            if (!$rates) {
                // rate note found
                $paymentObj = new PaymentObj();
                $paymentObj->no = 'Rates not found!';
                return collect(['error'=>$paymentObj]);
            }

            $payments = PaymentLog::leftJoin('users as cashier', 'payment_log.created_by', '=', 'cashier.id')
                ->leftJoin('users as client', 'payment_log.client_id', '=', 'client.id')
                ->leftJoin('currency', 'payment_log.currency_id', '=', 'currency.id')
                ->leftJoin('package', 'payment_log.package_id', '=', 'package.id')
                ->whereDate('payment_log.created_at', '>=', $from_date)
                ->whereDate('payment_log.created_at', '<=', $to_date)
                ->where('payment_log.client_id', '<>', 121514)
                ->whereNull('payment_log.deleted_by')
                ->whereNull('package.deleted_by')
                ->select(
                    'payment_log.payment',
                    'payment_log.currency_id',
                    'currency.name as currency',
                    'payment_log.type',
                    'payment_log.client_id as suite',
                    'client.name as client_name',
                    'client.surname as client_surname',
                    'package.internal_id as package',
                    'cashier.name as cashier_name',
                    'cashier.surname as cashier_surname',
                    'payment_log.created_at'
                )
                ->get();

            $payments_arr = array();
            $i = 0;
            foreach ($payments as $payment) {
                $i++;

                $rate_to_azn = $this->calculate_exchange_rate($rates, $payment->currency_id, 3);
                $payment_azn = $payment->payment * $rate_to_azn;
                $payment_azn = sprintf('%0.2f', $payment_azn);

                $cashier = $payment->cashier_name . ' ' . $payment->cashier_surname;
                if ($payment->type == 1) {
                    $payment_type = 'Cash';
                } else if ($payment->type == 2) {
                    $payment_type = 'POS Term';
                } else {
                    $payment_type = 'From balance';
                    $cashier = '';
                }

                $paymentObj = new PaymentObj();
                $paymentObj->no = $i;
                $paymentObj->suite = $payment->suite;
                $paymentObj->client = $payment->client_name . ' ' . $payment->client_surname;
                $paymentObj->package = $payment->package;
                $paymentObj->payment = $payment->payment;
                $paymentObj->currency = $payment->currency;
                $paymentObj->payment_azn = $payment_azn;
                $paymentObj->type = $payment_type;
                $paymentObj->cashier = $cashier;
                $paymentObj->created_date = substr($payment->created_at, 0, 16);

                array_push($payments_arr, $paymentObj);
            }

            return collect($payments_arr);
        } catch (\Exception $e) {
            $paymentObj = new PaymentObj();
            $paymentObj->no = 'Something went wrong!';
            return collect(['error'=>$paymentObj]);
        }
    }

    public function columnFormats(): array
    {
        return [
            'J' => NumberFormat::FORMAT_DATE_DATETIME
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Suite',
            'Client',
            'Package',
            'Payment',
            'Currency',
            'Payment (AZN)',
            'Payment type',
            'Cashier',
            'Date'
        ];
    }
}
