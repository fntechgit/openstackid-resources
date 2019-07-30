<?php namespace App\Mail;
/**
 * Copyright 2019 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use Illuminate\Support\Facades\Config;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use models\summit\SummitAttendee;
use models\summit\SummitAttendeeTicket;
/**
 * Class RevocationTicketEmail
 * @package App\Mail
 */
class RevocationTicketEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $tries = 1;

    /**
     * @var string
     */
    public $owner_email;

    /**
     * @var string
     */
    public $owner_full_name;

    /**
     * @var string
     */
    public $ticket_number;

    /**
     * @var string
     */
    public $summit_name;

    /**
     * @var string
     */
    public $summit_logo;

    /**
     * @var string
     */
    public $support_email;

    /**
     * RevocationTicketEmail constructor.
     * @param SummitAttendee $attendee
     * @param SummitAttendeeTicket $ticket
     */
    public function __construct(SummitAttendee $attendee, SummitAttendeeTicket $ticket)
    {
        $this->owner_email     = $attendee->getEmail();
        $this->owner_full_name = $attendee->getFullName();
        $this->ticket_number   = $ticket->getNumber();
        $this->summit_name     = $ticket->getOrder()->getSummit()->getName();
        $this->summit_logo     = $ticket->getOrder()->getSummit()->getLogoUrl();
        $this->support_email   = Config::get("registration.support_email", null);

        if(empty($this->support_email))
            throw new \InvalidArgumentException("missing support_email value");
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = sprintf('[%1$s] Your registration for %1$s has been cancelled', $this->summit_name);

        return $this->from($this->support_email)
            ->to($this->owner_email)
            ->subject($subject)
            ->view('emails.registration.revocation_ticket');
    }
}
