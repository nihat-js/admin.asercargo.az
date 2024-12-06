<?php

namespace App\Jobs;

use App\Services\Carrier;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

/**
 * Class SendToDGK
 * @package App\Jobs
 */
class SendToDGK implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var mixed
     */
    private $url;
    /**
     * @var mixed
     */
    private $package;

    /**
     * Create a new job instance.
     *
     * @param $package
     */
    public function __construct($package)
    {
        $this->package = $package;
    }

    /**
     * Execute the job.
     *
     * @param Carrier $carrier
     * @return void
     */
    public function handle(Carrier $carrier)
    {
        $carrier->addPackages($this->package);
    }
}
