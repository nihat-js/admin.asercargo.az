<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CollectorInWarehouseEmail extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */

	public $title;
	public $subject;
	public $content;
	public $bottom;
	public $button;

	public function __construct($title, $subject, $content, $bottom, $button)
	{
		$this->title = $title;
		$this->subject = $subject;
		$this->content = $content;
		$this->bottom = $bottom;
		$this->button = $button;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */

	public function build()
	{
		return $this->markdown('emails.collector')
				->from('noreply@asercargo.az', $this->title)
				->subject($this->subject)
				->with(['content' => $this->content, 'bottom' => $this->bottom, 'button_text' => $this->button]);
	}
}
