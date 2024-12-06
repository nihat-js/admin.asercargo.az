<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderInBaku extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public $track;
	public $client;

	public function __construct($track, $client)
	{
		$this->track = $track;
		$this->client = $client;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('emails.order_in_baku')
				->from('noreply@asercargo.az', "Aser Cargo Express")
				->with(['track' => $this->track, 'client' => $this->client]);
	}
}
