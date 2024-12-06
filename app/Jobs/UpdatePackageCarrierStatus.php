<?php

namespace App\Jobs;

use App\Package;
use App\PackageCarrierStatusTracking;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class UpdatePackageCarrierStatus
 * @package App\Jobs
 */
class UpdatePackageCarrierStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var
     */
    private $packageIds;
    private $statusId;
    /**
     * @var mixed|null
     */
    private $regNumber;

    /**
     * Create a new job instance.
     *
     * @param $ids
     * @param $statusId
     */
    public function __construct($ids, $statusId)
    {
        $this->packageIds = $ids;
        $this->statusId = $statusId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Package::whereIn('internal_id', $this->packageIds)->update([
            'carrier_status_id' => $this->statusId
        ]);
        switch ($this->statusId) {
            case 4:
                {
                    $note = 'posted';
                }
                break;
            case 7:
                {
                    $note = 'added_to_box';
                }
                break;
            case 8:
                {
                    $note = 'depesh';
                }
                break;
            case 10:
                {
                    $note = 'depesh_commercial';
                }
                break;
            default:
                {
                    $note = $this->statusId;
                }
                break;
        }
        $trackingData = [];
        foreach ($this->packageIds as $packageId) {
            $trackingData[] = [
                'package_id' => null,
                'internal_id' => $packageId,
                'carrier_status_id' => $this->statusId,
                'note' => $note,
                'created_at' => Carbon::now()
            ];
        }
        PackageCarrierStatusTracking::insert($trackingData);
    }
}
