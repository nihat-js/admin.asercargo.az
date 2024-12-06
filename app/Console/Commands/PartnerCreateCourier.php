<?php

namespace App\Console\Commands;

use App\Package;
use App\Services\PartnerCourierService;
use App\User;
use Illuminate\Console\Command;

class PartnerCreateCourier extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:partnercourier';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    private PartnerCourierService $partner;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PartnerCourierService $partner)
    {
        parent::__construct();
        $this->partner = $partner;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $packages = Package::whereNotIn('partner_id', [0])
           // ->whereIn('client_id', [142712, 152029])
            ->where([
                'payment_receipt' => null,
                'last_status_id' => 15,
                'in_baku' => 1,
                'is_warehouse' => 3,
                'has_courier' => 0
            ])
            ->select([
                'package.id as package_id', 'package.last_status_id', 'package.client_id',
                'package.internal_id', 'package.gross_weight'
            ])
            ->get();

        $packagesByClient = $packages->groupBy('client_id');

        //dd($packagesByClient);

        $this->partner->PartnerCourier($packagesByClient);


    }
}
