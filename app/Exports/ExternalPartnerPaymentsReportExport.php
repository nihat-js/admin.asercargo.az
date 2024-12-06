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

class ExternalPartnerPaymentsReportExport implements FromCollection, WithHeadings, ShouldAutoSize, WithColumnFormatting
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


            $payments = DB::table('PartnerFinanceReport')
                ->whereDate('createdAt', '>=', $from_date)
                ->whereDate('createdAt', '<=', $to_date)
                ->get();
            //dd($payments);
            $payments_arr = array();

            array_walk($payments, function ($paymentGroup) use (&$payments_arr) {
                array_walk($paymentGroup, function ($payment) use (&$payments_arr) {
                    $paymentObj = new ExternalPartnerPaymentObj();
                    $paymentObj->partner = $payment->Partner;
                    $paymentObj->flight = $payment->Flight;
                    $paymentObj->mawb = $payment->MAWB;
                    $paymentObj->container = $payment->Container;
                    $paymentObj->originalTrack = $payment->OriginalTrack;
                    $paymentObj->internalTrack = $payment->InternalTrack;
                    $paymentObj->status = $payment->Status;
                    $paymentObj->azerpostTrack = $payment->AzerpostTrack;
                    $paymentObj->grossWeight = $payment->GrossWeight;
                    $paymentObj->amount = $payment->Amount;
                    $paymentObj->partnerPickUp = $payment->PartnerPickUp;
                    $paymentObj->created_at = $payment->CreatedAt;

                    $payments_arr[] = $paymentObj;
                    unset($payment);
                });
            });

            return collect($payments_arr);
        } catch (\Exception $e) {
            //dd($e);
            $paymentObj = new OnlinePaymentObj();
            $paymentObj->partner = 'Something went wrong!';
            return collect(['error'=>$paymentObj]);
        }
    }

    public function columnFormats(): array
    {
        return [
            //'J' => NumberFormat::FORMAT_DATE_DATETIME
        ];
    }

    public function headings(): array
    {
        return [
            'Partner',
            'Flight',
            'Mawb',
            'Container',
            'Original Track',
            'Internal Track',
            'Status',
            'Azerpost Track',
            'Gross Weight',
            'Amount',
            'Partner PickUp',
            'CreatedAt',
        ];
    }
}
