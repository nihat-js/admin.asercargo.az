<?php

namespace App\Console\Commands;

use App\Countries;
use App\EmailListContent;
use App\Jobs\CollectorInWarehouseJob;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ClientMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client:send_mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        // $client = User::where('id', 116685)->first();


        $emails = EmailListContent::where(['type' => 'not_declared_notification'])->first();
			
        if ($emails) {
            $country_check = Countries::where('id', 7)->select('name_az')->first();
    
            if ($country_check) {
                $country_name = $country_check->name_az;
            } else {
                $country_name = '---';
            }
            $store_name = "hepsiburada";
            $track = "WWEAZ0008833114YQ";
            $client = "Inara Taghiyeva";
            $internal = "CBR279233";

            $email_to = "alifa@code.edu.az";
            $email_title = $emails->{'title_' . 'az'}; //from
            $email_subject = $emails->{'subject_' . 'az'};
            $email_subject = str_replace('{country_name}', $country_name, $email_subject);
            $email_bottom = $emails->{'content_bottom_' . 'az'};
            $email_content = $emails->{'content_' . 'az'};

            $email_content = str_replace('{client_name}', $client, $email_content);
            $email_content = str_replace('{seller}', $store_name, $email_content);
            $email_content = str_replace('{tracking}', $track, $email_content);
            $email_content = str_replace('{country}', $country_name, $email_content);
            $email_content = str_replace('{internal}', $internal, $email_content);

            $job = (new CollectorInWarehouseJob($email_to, $email_title, $email_subject, $email_content, $email_bottom))
                ->delay(Carbon::now()->addSeconds(10));
            dispatch($job);
        }
    }
}
