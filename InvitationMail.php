<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Invite;

class InvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Invite $invite;

    /**
     * Create a new message instance.
     */
    public function __construct(Invite $invite)
    {
        $this->invite = $invite;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $registrationLink = url('/register/' . $this->invite->token);

        return $this->subject('You are invited to join')
                    ->view('emails.invitation')
                    ->with([
                        'invite' => $this->invite,
                        'registrationLink' => $registrationLink,
                    ]);
    }
}
