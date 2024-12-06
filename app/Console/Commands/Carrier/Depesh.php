<?php

namespace App\Console\Commands\Carrier;

use App\Package;
use App\PackageCarrierStatusTracking;
use App\Services\Carrier;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Class Depesh
 * @package App\Console\Commands
 */
class Depesh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carrier:depesh';

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
        $packages = Package::whereNotNull('carrier_registration_number')
            ->whereIn('carrier_status_id', [7])
            ->with([
                'status' => function ($query) {
                    $query->where('status_id', 14);
                }
            ])
            ->with('container.flight')
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->orderBy('created_at', 'desc')
            ->select([
                'package.id',
                'package.internal_id',
                'package.carrier_status_id',
                'package.carrier_registration_number',
                'package.last_status_id',
                'package.container_id'
            ])->get();

        $request = [];
	    $requestArray = [];
	    $failedPackages = [];
        foreach ($packages as $package) {
	        if (count($package->status) == 0) {
		        continue;
	        }
	        if(!$package->container) {
		        continue;
            }
	        if (!$package->container->flight) {
                $failedPackages[] = [
                    'package_id' => $package->id,
                    'note' => 'flight_of_container_not_found',
                    'created_at' => Carbon::now()
                ];
		        continue;
	        }
	        if (!$package->container->flight->closed_at) {
                continue;
            }
            if (!$package->container->flight->awb) {
                continue;
            }
            $request[] = [
                'regNumber' => $package->carrier_registration_number,
                'trackingNumber' => $package->internal_id,
                'airWayBill' => $package->container->flight->awb,
                'depeshNumber' => 'CCN-' . $package->container_id
            ];
        }

        if (count($request) >= config('customs.depesh.max')) {
            $requestArray = array_chunk($request, config('customs.depesh.max'));
        } else {
            $requestArray[] = $request;
        }
        foreach ($requestArray as $data) {
            if (count($requestArray) > 1) {
                sleep(2);
            }
            $this->carrier->depesh($data);
        }
        PackageCarrierStatusTracking::insert($failedPackages);
    }
}
