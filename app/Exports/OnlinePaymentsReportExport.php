<?php

namespace App\Exports;

use App\ExchangeRate;
use App\PaymentLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class OnlinePaymentsReportExport implements FromCollection, WithHeadings, ShouldAutoSize, WithColumnFormatting
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
            $from_date = $this->from_date;
            $to_date = $this->to_date;


            $payments = DB::table('PaymentTask')
                ->whereDate('createdAt', '>=', $from_date)
                ->whereDate('createdAt', '<=', $to_date)
                ->get();
            //dd($payments);
            $payments_arr = array();

            array_walk($payments, function ($paymentGroup) use (&$payments_arr) {
                array_walk($paymentGroup, function ($payment) use (&$payments_arr) {
                    $paymentObj = new OnlinePaymentObj();
                    $paymentObj->suite = $payment->Suit;
                    $paymentObj->client = $payment->UserName;
                    $paymentObj->pan = $payment->Pan;
                    $paymentObj->type = $payment->Type;
                    $paymentObj->paymentType = $payment->PaymentType;
                    $paymentObj->operationResult = $payment->OperationResult;
                    $paymentObj->amount = $payment->Amount;
                    $paymentObj->created_at = $payment->CreatedAt;

                    $payments_arr[] = $paymentObj;
                });
            });

            return collect($payments_arr);
        } catch (\Exception $e) {
            //dd($e);
            $paymentObj = new OnlinePaymentObj();
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
            'Suite',
            'Client',
            'Pan',
            'Payment Type',
            'Type',
            'Operation Result',
            'Amount',
            'Date'
        ];
    }
}
