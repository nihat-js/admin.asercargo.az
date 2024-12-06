<?php

namespace App\Console\Commands;

use App\Services\Carrier;
use Illuminate\Console\Command;

class GroupList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carrier:groupList';

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
     * @return void
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
        $this->carrier->getGroupList();
    }
}
