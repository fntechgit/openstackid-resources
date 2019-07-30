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
 * Class SummitOrderReminderEmail
 * @package App\Mail\Registration\Reminders
 */
class SummitOrderReminderEmail extends Mailable
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
    public $summit_name;

    /**
     * @var string
     */
    public $manage_orders_url;


    /**
     * @var array
     */
    public $tickets;

    /**
     * SummitOrderReminderEmail constructor.
     * @param SummitOrder $order
     */
    public function __construct(SummitOrder $order)
    {
        $this->owner_full_name   = $order->getOwnerFullName();
        $this->owner_email       = $order->getOwnerEmail();
        $this->support_email     = Config::get("registration.support_email", null);
        $this->summit_name       = $order->getSummit()->getName();

        $base_url = Config::get("registration.dashboard_base_url", null);
        if(empty($base_url))
            throw new \InvalidArgumentException("missing dashboard_base_url value");

        $back_url = Config::get("registration.dashboard_back_url", null);
        if(empty($back_url))
            throw new \InvalidArgumentException("missing dashboard_back_url value");

        $this->manage_orders_url = sprintf($back_url, $base_url);

        if(empty($this->support_email))
            throw new \InvalidArgumentException("missing support_email value");

        $this->tickets = [];

        foreach ($order->getTickets() as $ticket){
            if(!$ticket->hasTicketType()) continue;

            $ticket_dto = [
                'number'           => $ticket->getNumber(),
                'ticket_type_name' => $ticket->getTicketType()->getName(),
                'has_owner'        => false,
                'price'            => $ticket->getFinalAmount(),
                'currency'         => $ticket->getCurrency(),
                'need_details'     => false,
            ];

            if($ticket->hasPromoCode()){
                $promo_code = $ticket->getPromoCode();
                $promo_code_dto = [
                    'code'        => $promo_code->getCode(),
                    'is_discount' => false,
                ];

                if($promo_code instanceof SummitRegistrationDiscountCode){
                    $promo_code_dto['is_discount']     = true;
                    $promo_code_dto['discount_amount'] = $promo_code->getAmount();
                    $promo_code_dto['discount_rate']   = $promo_code->getRate();
                }

                $ticket_dto['promo_code'] = $promo_code_dto;
            }

            if($ticket->hasOwner()){
                $ticket_dto['has_owner']        = true;
                $ticket_owner                   = $ticket->getOwner();
                $ticket_dto['owner_email']      = $ticket_owner->getEmail();
                $ticket_dto['owner_first_name'] = $ticket_owner->getFirstName();
                $ticket_dto['owner_last_name']    = $ticket_owner->getSurname();
                $ticket_dto['need_details']     = $ticket_owner->needToFillDetails();
            }

            $this->tickets[] = $ticket_dto;

        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = sprintf('[%1$s] Attention! Some attendees to %1$s in your order still need to accept their ticket', $this->summit_name);

        $mail = $this->from($this->support_email)
            ->to($this->owner_email)
            ->subject($subject)
            ->view('emails.registration.order_reminder_email');

        return $mail;
    }
}