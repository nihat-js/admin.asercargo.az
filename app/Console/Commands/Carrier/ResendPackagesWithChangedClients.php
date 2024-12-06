<?php

namespace App\Console\Commands\Carrier;

use App\Package;
use App\PackageStatus;
use App\Services\Carrier;
use Illuminate\Console\Command;

class ResendPackagesWithChangedClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carrier:resend';

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
     * @return void
     */
    public function handle(): void
    {
        $packages = Package::with('client')
            ->where('carrier_status_id', 9)
            ->whereNotIn('last_status_id', [41])
            ->whereNull('delivered_at')
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->where('updated_at', '>=', '2021-01-01')
            ->get();
        $packageIds = [];

        foreach ($packages as $package) {
            if (!$package->client) {
                continue;
            }
            if ($package->client->passport_fin) {
                if ($this->carrier->destroyPackage($package->id)) {
                    $packageIds[] = $package->getKey();
                    PackageStatus::create([
                        'package_id' => $package->id,
                        'status_id' => 37,
                        'created_by' => 1
                    ]);
                }
	    sleep(1);
            }
        }
        PackageStatus::whereIn('package_id', $packageIds)
            ->whereIn('status_id', config('customs.package.package_statuses'))
            ->delete();
        Package::whereIn('id', $packageIds)->update([
            'carrier_status_id' => 0,
            'carrier_registration_number' => 0
        ]);
    }
}
