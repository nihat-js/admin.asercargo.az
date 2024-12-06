<?php

namespace App\Console\Commands\Carrier;

use App\Package;
use App\PackageCarrierStatusTracking;
use App\Services\Carrier;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Class SendToCustoms
 * @package App\Console\Commands
 */
class SendToCommercial extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carrier:commercial';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send packages to DGK';
    /**
     * @var Carrier
     */
    private Carrier $carrier;

    /**
     * Create a new command instance.
     *
     * @param Carrier $carrier
     */
    public function __construct(Carrier $carrier)
    {
        parent::__construct();
        $this->carrier = $carrier;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $packages = Package::where('carrier_status_id', '!=', 10)
            ->with([
                    'client' => function ($query) {
                            $query->where('passport_series', 'LIKE' ,'VOEN');
                            $query->whereNotNull('passport_number');
                        }, 'collector', 'seller'
                ])
            ->with('container.flight')
            ->where('last_status_id', 14)
            ->where('customer_type_id', 2)
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->orderBy('created_at', 'desc')
            ->get();
            //cdd($packages);
            $packagesData = [];
            $failedPackages = [];

            foreach ($packages as &$package) {
                if (!$package->item) {
                    continue;
                }
                if (!$package->client) {
                    continue;
                }
                if (!$package->internal_id) {
                    continue;
                }
                if (!$package->client->passport_fin) {
                    continue;
                }
                if (!$package->client->passport_number) {
                    continue;
                }
                /*if (!$package->client->passport_series == "Voen") {
                    continue;
                }*/
                if (!in_array(strtolower($package->client->passport_series), ['voen'])) {
                    continue;
                }

                if (!$package->amount_usd) {
                    continue;
                }

                if (!$package->seller) {
                    $seller = $package->other_seller ?? '---';
                } else {
                    $seller = $package->seller->title ?? '---';
                }
                if (!$package->container->flight) {
                    continue;
                }
                if (!$package->container->flight->closed_at) {
                    continue;
                }
                if (!$package->container->flight->awb) {
                    continue;
                }
                //dd($package->item->price);
                $packageData = array();
                $packageData['tracking_id'] = $package->internal_id;
                $packageData['shipping_amount'] = $package->amount_usd;
                $packageData['weight'] = $package->gross_weight;
                $packageData['quantity'] = $package->item->quantity;
                $packageData['invoice_price'] = $package->item->price ?? 0;
                $packageData['fin'] = $package->client->passport_fin;
                $packageData['voen'] = $package->client->passport_number;
                $packageData['airWayBill'] = $package->container->flight->awb;
                $packageData['depeshNumber'] = 'CCN-' . $package->container_id;
                $packageData['import_name'] = ($package->client->name ?? '---') . " " . ($package->client->surname ?? "---");
                $packageData['title'] = str_ireplace(["(", ")", "\"", "print"], "-", $package->item->title ?? ' unknown ');
                $packageData['title'] = str_ireplace(["and", "or", "&"], "+", $packageData['title']);
                $packageData['title'] = str_ireplace(["а", "ж", "и"], "?", $packageData['title']);
                $packageData['import_address'] = $package->client->address1 ?? 'unknown';
                $packageData['document_type'] = 'PinCode';
                $packageData['phone'] = $package->client->phone1;
                $packageData['export_name'] = $seller;
                $packageData['export_address'] = isset($package->collector) ? $package->collector->location_address() : '---';
                $packageData['goods_fr_id'] = $package->country_id;
                $packageData['goods_to_id'] = "031";
                $packageData['goodslist'][] = [
                    "goods_id" => 1,
                    "name_of_goods" => $packageData['title'] ?? $packageData['import_address']
                ];
                $packageData['currency_id'] = $package->item->currency_id ?? 1;
                $packagesData[] = $packageData;
            }
            //dd(count($packagesData));
            if (count($packagesData) >= config('customs.post_packages.max')) {
                $packagesArray = array_chunk($packagesData, config('customs.post_packages.max'));
            } else {
                $packagesArray[] = $packagesData;
            }
            foreach ($packagesArray as $item) {
                //dd($item);
                $this->carrier->addCommercialPackages($item);
            }
            PackageCarrierStatusTracking::insert($failedPackages);
        } catch (\Exception $exception) {
            Log::error('customs_add_package_command_fail', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ]);
        }
    }
}
