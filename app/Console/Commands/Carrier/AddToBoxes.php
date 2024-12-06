<?php

namespace App\Console\Commands\Carrier;

use App\Package;
use App\Services\Carrier;
use Illuminate\Console\Command;

class AddToBoxes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carrier:addToBoxes';

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
            ->whereNotIn('carrier_registration_number', [0, 1])
            ->whereNotIn('carrier_status_id', [0, 4, 7, 8, 9, 10])
//            ->with([
//                'status' => function ($query) {
//                    $query->where('status_id', 5);
//                }
//	        ])
            ->where('last_status_id', 5)
            ->whereNotNull('internal_id')
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
	        ->whereNull('delivered_at')
	        ->where('updated_at', '>=', '2021-01-01 00:00:00')
            ->orderBy('created_at', 'desc')
            ->select([
                'package.id',
                'package.internal_id',
                'package.carrier_status_id',
                'package.carrier_registration_number',
                'package.last_status_id'
            ])->get();

        $request = [];

        foreach ($packages as $package) {
            if (count($package->status) == 0) {
                continue;
            }

            $request[] = [
                'tracking_number' => $package->internal_id,
                'registration_number' => $package->carrier_registration_number
            ];
        }

        if (count($request) >= config('customs.add_to_box.max')) {
            $packagesArray = array_chunk($request, config('customs.add_to_box.max'));
        } else {
            $packagesArray[] = $request;
        }

        foreach ($packagesArray as $item) {
  	        $this->carrier->addToBoxes($item);
        }
    }
}

