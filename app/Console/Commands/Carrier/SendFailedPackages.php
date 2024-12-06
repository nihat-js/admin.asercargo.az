<?php

namespace App\Console\Commands\Carrier;

use App\Currency;
use App\ExchangeRate;
use App\Item;
use App\Package;
use App\Services\Carrier;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

class SendFailedPackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carrier:failed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * @var Carrier
     */
    private Carrier $carrier;
    /**
     * @var Currency[]|Collection|null
     */
    private $currency;

    /**
     * Create a new command instance.
     *
     * @param Carrier $carrier
     */
    public function __construct(Carrier $carrier)
    {
        parent::__construct();
        $this->carrier = $carrier;
        $this->currency = (Schema::hasTable('currency')) ? Currency::all() : null;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $packages = Package::with('client')
            ->whereIn('id', [204600])
            ->get();
        $tracks = [];
        foreach ($packages as $package) {
            sleep(2);
            if ($package->client->passport_fin) {
                $result = $this->carrier->getDeclarations(null, $package->internal_id);

                $itemRate = null;
                $rate = null;
                if (isset($result[0])) {
                    if ($item = Item::where('package_id', $package->id)->first()) {
                        $currencyId = $item->currency_id;
                        $currency = $this->currency->where('code', $result[0]['goodsList'][0]['currencyType'])->first();
                        if (!$currency) {
                            continue;
                        } else {
                            $currencyFromCustoms = $currency->id;
                        }
dd($currencyId, $currencyFromCustoms);
                        if ($currencyFromCustoms != 1) {
                            $date = Carbon::now();
                            $rate = ExchangeRate::whereDate('from_date', '<=', $date)
                                ->whereDate('to_date', '>=', $date)
                                ->where(['from_currency_id' => $currencyFromCustoms, 'to_currency_id' => 1]) //to USD
                                ->select('rate')
                                ->orderBy('id', 'desc')
                                ->first();
                        } else {
                            $defaultRate = 1;
                        }
                        if ($currencyId != 1) {
                            $date = Carbon::now();
                            $itemRate = ExchangeRate::whereDate('from_date', '<=', $date)
                                ->whereDate('to_date', '>=', $date)
                                ->where(['from_currency_id' => $currencyFromCustoms, 'to_currency_id' => $currencyId]) //to USD
                                ->select('rate')
                                ->orderBy('id', 'desc')
                                ->first();
                        } else {
                            $itemDefaultRate = 1;
                        }
                    }
                    if (isset($rate)) {
                        $currencyRate = $rate->rate;
                    } elseif (isset($defaultRate)) {
                        $currencyRate = 1;
                    } else {
                        $currencyRate = 1;
                    }
                    if (isset($itemRate)) {
                        $itemCurrencyRate = $itemRate->rate;
                    } elseif (isset($itemDefaultRate)) {
                        $itemCurrencyRate = 1;
                    } else {
                        $itemCurrencyRate = 1;
                    }
                    $totalInvoicePrice = 0;
                    $totalInvoicePriceUsd = 0;
                    foreach ($result[0]['goodsList'] as $items) {
                        $totalInvoicePriceUsd += $items['invoicePrice'] * $currencyRate;
                        $totalInvoicePrice += $items['invoicePrice'] * $itemCurrencyRate;
                    }
                    $updated = Item::where('package_id', $package->id)->update([
                        'price' => $totalInvoicePrice,
                        'price_usd' => $totalInvoicePriceUsd
                    ]);
                    if ($updated) {
                        $tracks[] = $package->internal_id;
                    }
                }
            }
        }

        dd($tracks);
    }
}
