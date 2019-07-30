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
use Illuminate\Contracts\Queue\ShouldQueue;
use models\summit\SummitAttendeeTicket;
/**
 * Class SummitTicketRefundRequestAdmin
 * @package App\Mail
 */
class SummitTicketRefundRequestAdmin extends Mailable
{
    public $tries = 1;

    use Queueable, SerializesModels;

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
     * SummitTicketRefundRequestAdmin constructor.
     * @param SummitAttendeeTicket $ticket
     */
    public function __construct(SummitAttendeeTicket $ticket)
    {
        $order                 = $ticket->getOrder();
        $this->owner_full_name = $order->getOwnerFullName();
        $this->owner_email     = $order->getOwnerEmail();
        $this->ticket_number   = $ticket->getNumber();
        $this->summit_name     = $order->getSummit()->getName();
        $this->summit_logo     = $order->getSummit()->getLogoUrl();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = Config::get("registration.ticket_refund_requested_admin_email_subject");
        if(empty($subject))
            $subject = sprintf("[%s] - Ticket Refund Requested", $this->summit_name);
        $from = Config::get("mail.from");
        if(empty($from)){
            throw new \InvalidArgumentException("mail.from is not set");
        }
        $to = Config::get("registration.admin_email");
        if(empty($to)){
            throw new \InvalidArgumentException("registration.admin_email is not set");
        }
        $mail = $this->from($from)
            ->to($to)
            ->subject($subject)
            ->view('emails.registration.ticket_refund_requested_admin');

        return $mail;
    }

}
