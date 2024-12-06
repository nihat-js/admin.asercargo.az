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
class SendToCustoms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carrier:send';

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
            $packages = Package::whereIn('carrier_status_id', [0, 5])
                ->with([
		    'item' => function ($query) {
			$query->whereIn('invoice_status', [3, 4]);
		     },
		    'client' => function ($query) {
                    	$query->whereNotNull('passport_fin');
                }, 'collector', 'seller'])
                ->whereIn('last_status_id', [6, 11, 35, 37, 14, 5])
		        ->where('client_id', '!=', 0)
                ->where('customer_type_id', 1)
                ->where('amount_usd', '!=', 0)
                ->whereNull('deleted_at')
                ->whereNull('deleted_by')
                ->whereDate('updated_at', '>=', '2021-01-01')
                ->orderBy('created_at', 'desc')
                ->get();

            $packagesData = [];
            $failedPackages = [];

            foreach ($packages as &$package) {
                if (!$package->item) {
                    // $failedPackages[] = [
                    //     'package_id' => $package->id,
                    //     'note' => 'item_not_found',
                    //     'created_at' => Carbon::now()
                    // ];
                    continue;
                }
                if (!$package->client) {
//                    $failedPackages[] = [
//                        'package_id' => $package->id,
//                        'note' => 'client_not_found',
//                        'created_at' => Carbon::now()
//                    ];
                    continue;
                }
                if (!$package->internal_id) {
                    $failedPackages[] = [
                        'package_id' => $package->id,
                        'note' => 'internal_id_null',
                        'created_at' => Carbon::now()
                    ];
                    continue;
                }
                if (!$package->client->passport_fin) {
                    $failedPackages[] = [
                        'package_id' => $package->id,
                        'note' => 'client_fin_null',
                        'created_at' => Carbon::now()
                    ];
                    continue;
                }
                if (!$package->amount_usd) {
                    $failedPackages[] = [
                        'package_id' => $package->id,
                        'note' => 'shipping_amount_null',
                        'created_at' => Carbon::now()
                    ];
                    continue;
                }
                if (!$package->seller) {
                    $seller = $package->other_seller ?? '---';
                } else {
                    $seller = $package->seller->title ?? '---';
                }
                $packageData = array();
                $packageData['tracking_id'] = $package->internal_id;
                $packageData['shipping_amount'] = $package->amount_usd;
//                $packageData['weight'] = ($package->gross_weight > $package->volume_weight)
//                    ? $package->gross_weight : $package->volume_weight;
                $packageData['weight'] = $package->gross_weight;
                $packageData['quantity'] = $package->item->quantity;
                $packageData['invoice_price'] = $package->item->price ?? 0;
                $packageData['fin'] = $package->client->passport_fin;
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
            if (count($packagesData) >= config('customs.post_packages.max')) {
                $packagesArray = array_chunk($packagesData, config('customs.post_packages.max'));
            } else {
                $packagesArray[] = $packagesData;
            }
            foreach ($packagesArray as $item) {
                $this->carrier->addPackages($item);
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
