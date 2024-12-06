<?php

namespace App\Jobs;

use App\Package;
use App\PackageCarrierStatusTracking;
use App\PackageStatus;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use PhpOffice\PhpSpreadsheet\Theme;

class PackageStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $packageIds;
    private $closedUser;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($packageIds,$closedUser)
    {
        $this->packageIds = $packageIds;
        $this->closedUser = $closedUser;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pluck_ids = $this->packageIds->pluck('id');
        $trackingData = [];
        foreach ($pluck_ids as $packageId) {
            $trackingData[] = [
                'package_id' => $packageId,
                'status_id' => 14,
                'created_by' => $this->closedUser,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        PackageStatus::insert($trackingData);

        Package::whereIn('id', $pluck_ids)->update(['is_warehouse'=>2, 'on_the_way_date' => Carbon::now()]);
    }
}
