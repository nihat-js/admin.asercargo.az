<?php

namespace App\Console\Commands\Carrier;

use App\Services\Carrier;
use Illuminate\Console\Command;

/**
 * Class RefreshCarrierData
 * @package App\Console\Commands
 */
class RefreshCarrierData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carrier:refresh';

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
        $this->carrier->checkPackagesStatuses();
    }
}
