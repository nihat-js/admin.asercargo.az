<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SpecialOrderMail extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public $order_id;
	public $client;
	public $message;

	public function __construct($order_id, $client, $message)
	{
		$this->order_id = $order_id;
		$this->client = $client;
		$this->message = $message;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('emails.special_order')
				->from('noreply@asercargo.az', "Aser Cargo Express")
				->subject("Sifariş et xidməti")
				->with(['order_id' => $this->order_id, 'client' => $this->client, 'message' => $this->message]);
	}
}
