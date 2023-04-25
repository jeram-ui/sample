<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\log;
class TrackerMailable extends Mailable
{
    use Queueable, SerializesModels;
    public $token;
    public $subjects;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        log::debug($data['email']);
        $this->token = $data['email'];
        // $this->$subjects =  $data['email'];
    }
    public function build()
    {
        return $this->subject('Password Reset')->view('mail');
    }
}
