<?php

namespace App\Services;

use App\ExchangeRate;
use App\Package;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CalcWarehouseDebtService
{

    public function ExternalDebt(): void
    {
        $useType = $this->warehouseDebt('external');

        $packages = Package::leftJoin('item', 'package.id', 'item.package_id')
            ->whereDate('package.created_at', '>', '2023-12-01 00:00:00')
            ->whereNull('package.deleted_by')
            ->where(function ($query) {
                $query->whereIn('package.last_status_id', [37, 41])
                    ->where(function ($query) {
                        $query->whereNull('item.invoice_status')
                            ->orWhere('item.invoice_status', '<>', 3)
                            ->orWhere('item.invoice_status', '<>', 4);
                    });
            })
            ->whereDate('package.created_at', '<', now()->subDays($useType->limitDay)->format('Y-m-d'))
            ->where('client_id', '!=', 0)
            ->whereNull('package.partner_id')
            //->where('client_id', 142712)
            //->whereIn('internal_id', ['CBR647254',])
            ->select(
                'package.id', 'package.created_at',
                'package.external_w_debt',
                'package.external_w_debt_flag',
                'package.external_w_debt_flag',
                'package.external_w_debt_azn',
                'package.external_w_debt_day',
                'package.paid_status'

            )
            ->get();
        //dd($packages);
        $packageChunks = $packages->chunk(100);
        foreach ($packageChunks as $chunk) {
            $this->updatePackagePricesAndWarehouseDebts($chunk, $useType);
        }
    }

    public function InternalDebt(): void
    {
        $useType = $this->warehouseDebt('internal');

        $packages = Package::whereDate('created_at', '>', '2023-12-01 00:00:00')
            ->whereNull('deleted_by')
            ->whereDate('created_at', '<', now()->subDays($useType->limitDay)->format('Y-m-d'))
            ->whereIn('last_status_id', [15])
            ->where('client_id', '!=', 0)
            ->whereNull('partner_id')
            //->where('client_id', 142712)
            ->where('in_baku', 1)
            ->whereNotNull('in_baku_date')
            ->whereNull('delivered_by')
            ->whereNull('delivered_at')
            ->where('has_courier', 0)
            // ->whereIn('internal_id', ['CBR664219',])
            ->select(
                'id', 'created_at',
                'internal_w_debt',
                'internal_w_debt_flag',
                'internal_w_debt_day',
                'internal_w_debt_usd',
                'paid_status'
            )
            ->get();
        //dd($packages);

        $packageChunks = $packages->chunk(100);
        foreach ($packageChunks as $chunk) {
            $this->updatePackagePricesAndWarehouseDebts($chunk, $useType);
        }
    }

    private function warehouseDebt(string $type)
    {
        $debt = DB::table('WarehouseCalcDebt')->where('type', $type)->first();
        return $debt;
    }


    private function updatePackagePricesAndWarehouseDebts($packages, $debtType)
    {
        $convertPriceCurrency = $this->checkCurreny($debtType->type);
        $packages->each(function ($package) use ($debtType, $convertPriceCurrency) {

            $useType = $debtType;
            $type = $useType->type;
            $created_at = Carbon::parse($package->created_at);
            $today = Carbon::now();

            $days_elapsed = $today->diffInDaysFiltered(function (Carbon $date) {
                return $date->dayOfWeek !== Carbon::SUNDAY;
            }, $created_at);
            $checkDay = max(0, $days_elapsed - $useType->limitDay);

            if ($checkDay == 0) {
                return;
            }

            if ($package->{$type.'_w_debt_flag'} < $useType->day && $package->{$type.'_w_debt_flag'} !== null) {
                $package->{$type.'_w_debt'} = $package->{$type.'_w_debt'};
                $package->{$type.'_w_debt_flag'} += 1;
                $package->{$type.'_w_debt_day'} += 1;
                return;
            }

            $price = $this->calculatePrice($useType, $package->{$type.'_w_debt_flag'}, $checkDay);

            if($type === "external"){
                $convertPrice = $this->ExchangeRate(1, 3, $price);
            }else{
                $convertPrice = $this->ExchangeRate(3,1, $price);
            }

            if($package->{$type.'_w_debt_flag'} === null)
            {
                $package->{$type.'_w_debt'} = $price;
                $package->{$type.'_w_debt_' . $convertPriceCurrency} = $convertPrice;
                $package->{$type.'_w_debt_flag'} = 1;
                $package->{$type.'_w_debt_day'} = $days_elapsed;
                $package->{'paid_status'} = 0;
            }

            if ($price !== null) {
                $package->{$type.'_w_debt'} += $price;
                $package->{$type.'_w_debt_' . $convertPriceCurrency} += $convertPrice;
                $package->{$type.'_w_debt_flag'} = 1;
                $package->{$type.'_w_debt_day'} += 1;
                $package->{'paid_status'} = 0;
            }
        });

        //dd($packages);
        $this->bulkUpdatePackages($packages, $debtType->type, $convertPriceCurrency);
    }

    private function calculatePrice($type, $flag, $checkDay)
    {
        $periods = null;

        if($flag === null)
        {
            $periods = floor($checkDay / $type->day);
        }

        if($flag !== null)
        {
            $periods = floor($flag / $type->day);
        }

        $price_per_period = $type->amount;
        $price = $periods * $price_per_period;

        return $price;
    }

    private function checkCurreny($type){
        if($type === "external"){
            $convertPriceCurrency = "azn";
        }else{
            $convertPriceCurrency = "usd";
        }

        return $convertPriceCurrency;
    }

    private function ExchangeRate($from, $to, $amount)
    {
        $date = Carbon::today();
        $rate = ExchangeRate::where(['from_currency_id' => $from, 'to_currency_id' => $to])
            ->whereDate('from_date', '<=', $date)->whereDate('to_date', '>=', $date)
            ->select('rate')
            ->first();

        if (!$rate) {
            $pay = 0;
        } else {
            $pay = $amount * $rate->rate;
        }
        $pay = sprintf('%0.2f', $pay);

        return $pay;
    }

    private function bulkUpdatePackages(Collection $packages, $type, $convertPriceCurrency)
    {

        $packages->each(function ($package) use ($type, $convertPriceCurrency) {
            Package::where('id', $package->id)->update([
                $type.'_w_debt' => $package->{$type.'_w_debt'},
                $type.'_w_debt_'.$convertPriceCurrency => $package->{$type.'_w_debt_'.$convertPriceCurrency},
                $type.'_w_debt_day' => $package->{$type.'_w_debt_day'},
                $type.'_w_debt_flag' => $package->{$type.'_w_debt_flag'},
                'paid_status' => $package->paid_status
            ]);
        });
    }

}