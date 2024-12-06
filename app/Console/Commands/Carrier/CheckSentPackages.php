<?php

namespace App\Console\Commands\Carrier;

use App\Package;
use App\Services\Carrier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Class CheckSentPackages
 * @package App\Console\Commands
 */
class CheckSentPackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'packages:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check packages from DGK';
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
    public function handle(): bool
    {
        $packages = $this->carrier->getAddedPackages();
        if (isset($packages['packages'])) {
            Package::whereIn('internal_id', $packages['packages'])->update([
                'carrier_status_id' => 4
            ]);
            Log::info('carrier_status_updated', [
                'count' => count($packages['packages']),
                'packages' => $packages['packages']
            ]);
        }

        return true;
    }
}
