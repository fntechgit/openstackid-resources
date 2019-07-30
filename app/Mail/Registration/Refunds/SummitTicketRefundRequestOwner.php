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
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;
use models\summit\SummitAttendeeTicket;
/**
 * Class SummitTicketRefundRequestOwner
 * @package App\Mail
 */
class SummitTicketRefundRequestOwner extends Mailable
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
    public $ticket_promo_code;

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
     * @var null|string
     */
    public $ticket_currency;

    /**
     * @var string
     */
    public $ticket_type_name;

    /**
     * @var float
     */
    public $ticket_amount;

    /**
     * @var null|string
     */
    public $ticket_owner;

    /**
     * SummitTicketRefundRequestOwner constructor.
     * @param SummitAttendeeTicket $ticket
     */
    public function __construct(SummitAttendeeTicket $ticket)
    {
        $order                 = $ticket->getOrder();
        $this->owner_full_name = $order->getOwnerFullName();
        $this->owner_email     = $order->getOwnerEmail();
        $this->summit_name     = $order->getSummit()->getName();
        $this->summit_logo     = $order->getSummit()->getLogoUrl();

        $this->ticket_number     = $ticket->getNumber();
        $this->ticket_type_name  = $ticket->getTicketType()->getName();
        $this->ticket_currency   = $ticket->getCurrency();
        $this->ticket_amount     = $ticket->getFinalAmount();

        if($ticket->hasPromoCode()){
            $this->ticket_promo_code = $ticket->getPromoCode()->getCode();
        }

        if($ticket->hasOwner()){
            $this->ticket_owner = $ticket->getOwner()->getFullName();
        }

        $this->support_email = Config::get("registration.support_email", null);

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

        $subject = sprintf('[%1$s] Your ticket refund request for %1$s has been received', $this->summit_name);

        $mail = $this->from($this->support_email)
            ->to($this->owner_email)
            ->subject($subject)
            ->view('emails.registration.ticket_refund_requested_owner');

        return $mail;
    }
}
