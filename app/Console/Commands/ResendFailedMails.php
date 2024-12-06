<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResendFailedMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resend:emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend emails';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $fails = DB::table('failed_jobs')->limit(60)->pluck('id');
        foreach ($fails as $id) {
            $this->call('queue:retry', ['id' => $id]);
        }
    }
}

