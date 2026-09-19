<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public ?string $reason;

    public function __construct(User $user, ?string $reason = null)
    {
        $this->user = $user;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->subject('GGCW Portal - Registration Update')
                    ->view('emails.user-rejected')
                    ->with([
                        'user' => $this->user,
                        'reason' => $this->reason,
                    ]);
    }
}