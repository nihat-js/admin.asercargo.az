<?php

namespace App\Jobs;

use App\Package;
use App\TrackingLog;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class WarehouseChangeStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $package_id;
    private $user_id;
    private $position_id;
    private $in_baku;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($package_id, $user_id, $position_id, $in_baku)
    {
        $this->package_id = $package_id;
        $this->user_id = $user_id;
        $this->position_id = $position_id;
        $this->in_baku = $in_baku;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        TrackingLog::create([
            'package_id' => $this->package_id,
            'operator_id' => $this->user_id,
            'position_id' => $this->position_id,
            'created_by' => $this->user_id
        ]);

        if ($this->in_baku == 0) {
            // not baku
            Package::where('id', $this->package_id)->update(['in_baku'=>1, 'in_baku_date'=>Carbon::now()]);
        }

        if ($this->position_id == 1) { // position for customs
            // detained at customs
            Package::where('id', $this->package_id)->update(['customs_date'=>Carbon::now()]);
        }
    }
}
