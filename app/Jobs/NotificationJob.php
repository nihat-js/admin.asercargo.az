<?php

namespace App\Jobs;

use App\Http\Controllers\NotificationController;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class NotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $client;
    public $title;
    public $subject;
    public $content;

    public function __construct($title, $subject, $content, $client)
    {
        $this->title = $title;
        $this->subject = $subject;
        $this->content = $content;
        $this->client = $client;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        Log::info([
//            $this->client,
//            'package_list_notif'
//        ]);
        $notificationService = new NotificationController();
        $notificationService->sendNotification($this->title, $this->subject, $this->content, $this->client);
    }
}
