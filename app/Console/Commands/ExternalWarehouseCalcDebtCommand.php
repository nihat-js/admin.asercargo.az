<?php

namespace App\Console\Commands;

use App\Services\CalcWarehouseDebtService;
use Illuminate\Console\Command;

class ExternalWarehouseCalcDebtCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calc:external';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate external warehouse debt';
    private CalcWarehouseDebtService $calcWarehouseDebtService;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CalcWarehouseDebtService $calcWarehouseDebtService)
    {
        parent::__construct();
        $this->calcWarehouseDebtService = $calcWarehouseDebtService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->calcWarehouseDebtService->ExternalDebt();
        sleep(2);
        $this->calcWarehouseDebtService->InternalDebt();
    }
}
