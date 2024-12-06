<?php

namespace App\Jobs;

use App\Mail\SpecialOrderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class SpecialOrderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $email;
    public $order_id;
    public $client;
    public $message;

    public function __construct($email, $order_id, $client, $message)
    {
        $this->email = $email;
        $this->order_id = $order_id;
        $this->client = $client;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)->send(new SpecialOrderMail($this->order_id, $this->client, $this->message));
    }
}
