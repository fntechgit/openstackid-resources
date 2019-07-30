<?php namespace App\Mail\Registration\Reminders;

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
use App\Http\Renderers\SummitAttendeeTicketPDFRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;
use models\summit\SummitAttendee;
use models\summit\SummitAttendeeTicket;
use models\summit\SummitOrder;
use models\summit\SummitRegistrationDiscountCode;
/**
 * Class SummitTicketReminderEmail
 * @package App\Mail\Registration\Reminders
 */
class SummitTicketReminderEmail  extends Mailable
{
    use Queueable, SerializesModels;

    public $tries = 1;

    /**
     * @var string
     */
    public $owner_full_name;

    /**
     * @var string
     */
    public $owner_email;

    /**
     * @var string
     */
    public $support_email;

    /**
     * @var string
     */
    public $edit_ticket_link;

    /**
     * @var string
     */
    public $summit_name;

    /**
     * SummitTicketReminderEmail constructor.
     * @param SummitAttendeeTicket $ticket
     */
    public function __construct(SummitAttendeeTicket $ticket)
    {
        $attendee                = $ticket->getOwner();
        $this->owner_full_name   = $attendee->getFullName();
        $this->owner_email       = $attendee->getEmail();
        $this->support_email     = Config::get("registration.support_email", null);
        $this->summit_name       = $ticket->getOrder()->getSummit()->getName();

        $base_url                = Config::get('registration.dashboard_base_url', null);
        $edit_ticket_link        = Config::get('registration.dashboard_attendee_edit_form_url', null);

        if(empty($base_url))
            throw new \InvalidArgumentException("missing dashboard_base_url value");
        if(empty($edit_ticket_link))
            throw new \InvalidArgumentException("missing dashboard_attendee_edit_form_url value");

        $this->edit_ticket_link  = sprintf($edit_ticket_link, $base_url, $ticket->getHash());

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
        $subject = sprintf('[%1$s] Attention! You need to accept your ticket to %1$s by entering the required details', $this->summit_name);

        $mail = $this->from($this->support_email)
            ->to($this->owner_email)
            ->subject($subject)
            ->view('emails.registration.ticket_reminder_email');

        return $mail;
    }
}