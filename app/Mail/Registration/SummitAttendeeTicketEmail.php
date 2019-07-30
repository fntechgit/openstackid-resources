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
use SimpleSoftwareIO\QrCode\Facades\QrCode;
/**
 * Class SummitAttendeeTicketEmail
 * @package App\Mail
 */
class SummitAttendeeTicketEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $tries = 1;

    /**
     * @var string
     */
    public $ticket_pdf_content;

    /**
     * @var string
     */
    public $summit_name;

    /**
     * @var null|string
     */
    public $summit_logo;

    /**
     * @var null|string
     */
    public $ticket_qr_data;

    /**
     * @var null|string
     */
    public $ticket_number;

    /**
     * @var string
     */
    public $ticket_type_name;

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
    public $promo_code;

    /**
     * @var float
     */
    public $ticket_amount;

    /**
     * @var null|string
     */
    public $ticket_currency;

    /**
     * @var string
     */
    public $support_email;

    /**
     * SummitAttendeeTicketEmail constructor.
     * @param SummitAttendeeTicket $ticket
     */
    public function __construct(SummitAttendeeTicket $ticket)
    {
        $attendee                 = $ticket->getOwner();
        $summit                   = $attendee->getSummit();
        $this->summit_name        = $summit->getName();
        $this->summit_logo        = $summit->getLogoUrl();
        $this->ticket_number      = $ticket->getNumber();
        $this->ticket_type_name   = $ticket->getTicketType()->getName();
        $this->ticket_amount      = $ticket->getFinalAmount();
        $this->ticket_currency    = $ticket->getCurrency();
        $this->owner_email        = $ticket->getOwner()->getEmail();
        $this->owner_full_name    = $ticket->getOwner()->getFullName();
        $this->promo_code         = $ticket->hasPromoCode() ? $ticket->getPromoCode()->getCode() : null;
        $this->support_email      = Config::get("registration.support_email", null);

        if(empty($this->support_email))
            throw new \InvalidArgumentException("missing support_email value");

        $renderer                 = new SummitAttendeeTicketPDFRenderer($ticket);
        $this->ticket_qr_data     = base64_encode(QrCode::format('png')->size(250,250)->generate($ticket->getQRCode()));
        $this->ticket_pdf_content = base64_encode($renderer->render());
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $subject              = sprintf('[%1$s] Your Ticket for %1$s', $this->summit_name);
        $this->ticket_qr_data = base64_decode($this->ticket_qr_data );

        $mail    = $this->from($this->support_email)
            ->to($this->owner_email)
            ->subject($subject)
            ->view('emails.registration.attendee_ticket');

        if(!empty($this->ticket_pdf_content)){
            $mail = $mail->attachData(base64_decode($this->ticket_pdf_content), 'ticket_'.  $this->ticket_number .'.pdf', [
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}