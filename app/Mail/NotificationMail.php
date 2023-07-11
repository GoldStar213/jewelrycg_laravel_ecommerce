<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ShippingOption;
use Gloudemans\Shoppingcart\Facades\Cart;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userId;
    public $message;

    public function __construct($userId, $subject, $message, $link)
    {
        $this->userId = $userId;
        $this->message = $message;
        $this->subjectcontent = $subject;
        $this->link = $link;
    }

    public function build()
    {
        $this->user = User::find($this->userId);

        return $this->subject($this->subjectcontent)
            ->view('emails.notification')
            ->with([
                'message_content' => $this->message,
                'username' => $this->user->username,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'link' => $this->link,
            ]);
    }
}
