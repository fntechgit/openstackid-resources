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
use App\Http\Renderers\SummitAttendeeTicketPDFRenderer;
use Illuminate\Support\Facades\Config;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use models\summit\SummitAttendeeTicket;
use models\summit\SummitRegistrationDiscountCode;
/**
 * Class InviteAttendeeTicketEditionMail
 * @package App\Mail
 */
class InviteAttendeeTicketEditionMail extends Mailable
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
    public $summit_name;

    /**
     * @var string
     */
    public $owner_full_name;

    /**
     * @var string
     */
    public $hash;

    /**
     * @var string
     */
    public $edit_ticket_link;

    /**
     * @var string
     */
    public $ticket_number;

    /**
     * @var string
     */
    public $ticket_type_name;

    /**
     * @var string
     */
    public $promo_code;

    /**
     * @var float
     */
    public $promo_code_discount_rate;

    /**
     * @var float
     */
    public $promo_code_discount_amount;

    /**
     * @var float|null
     */
    public $ticket_raw_amount;

    /**
     * @var null|string
     */
    public $ticket_currency;

    /**
     * @var float
     */
    public $ticket_discount;

    /**
     * @var float
     */
    public $ticket_taxes;

    /**
     * @var float
     */
    public $ticket_amount;

    /**
     * @var string
     */
    public $summit_logo;

    /**
     * @var string
     */
    public $support_email;

    /**
     * @var string
     */
    public $order_owner_full_name;

    /**
     * @var bool
     */
    public $need_details;

    /**
     * InviteAttendeeTicketEditionMail constructor.
     * @param SummitAttendeeTicket $ticket
     */
    public function __construct(SummitAttendeeTicket $ticket)
    {
        $owner                       = $ticket->getOwner();
        $order                       = $ticket->getOrder();
        $summit                      = $order->getSummit();
        $this->order_owner_full_name = $order->getOwnerFullName();
        $this->owner_full_name       = $owner->getFullName();
        $this->hash                  = $ticket->getHash();
        $this->owner_email           = $owner->getEmail();
        $this->summit_name           = $summit->getName();
        $this->summit_logo           = $summit->getLogoUrl();
        $base_url                    = Config::get('registration.dashboard_base_url', null);
        $edit_ticket_link            = Config::get('registration.dashboard_attendee_edit_form_url', null);

        if(empty($base_url))
            throw new \InvalidArgumentException("missing dashboard_base_url value");
        if(empty($edit_ticket_link))
            throw new \InvalidArgumentException("missing dashboard_attendee_edit_form_url value");

        $this->edit_ticket_link  = sprintf($edit_ticket_link, $base_url, $this->hash);
        $this->ticket_number     = $ticket->getNumber();
        $this->ticket_type_name  = $ticket->getTicketType()->getName();
        $this->ticket_raw_amount = $ticket->getRawCost();
        $this->ticket_currency   = $ticket->getCurrency();
        $this->ticket_discount   = $ticket->getDiscount();
        $this->ticket_taxes      = $ticket->getTaxesAmount();
        $this->ticket_amount     = $ticket->getFinalAmount();
        $this->need_details      = $owner->needToFillDetails();

        $promo_code = $ticket->hasPromoCode() ? $ticket->getPromoCode(): null;
        if(!is_null($promo_code)) {
            $this->promo_code = $promo_code->getCode();

            if ($promo_code instanceof SummitRegistrationDiscountCode) {
                $this->promo_code_discount_rate = $promo_code->getRate();
                $this->promo_code_discount_amount = $promo_code->getAmount();
            }
        }

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
        $subject = sprintf('[%1$s] You have been registered for %1$s', $this->summit_name);

        $mail    = $this->from($this->support_email)
            ->to($this->owner_email)
            ->subject($subject)
            ->view('emails.registration.invite_attendee_ticket_edition');

        return $mail;
    }
}
