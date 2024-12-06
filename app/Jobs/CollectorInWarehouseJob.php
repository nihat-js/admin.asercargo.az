<?php

namespace App\Jobs;

use App\Mail\CollectorInWarehouseEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CollectorInWarehouseJob implements ShouldQueue
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
    public $bottom;
    public $button;

    public function __construct($client, $title, $subject, $content, $bottom, $button = '')
    {
        $this->client = $client;
        $this->title = $title;
        $this->subject = $subject;
        $this->content = $content;
        $this->bottom = $bottom;
        $this->button = $button;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
       Mail::to($this->client)->send(new CollectorInWarehouseEmail($this->title, $this->subject, $this->content, $this->bottom, $this->button));
    }
}
