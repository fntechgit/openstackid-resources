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
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use models\summit\SummitOrder;
/**
 * Class UnregisteredMemberOrderPaidMail
 * @package App\Mail
 */
class UnregisteredMemberOrderPaidMail extends RegisteredMemberOrderPaidMail
{

    public $tries = 1;

    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $set_password_link;

    /**
     * UnregisteredMemberOrderPaidMail constructor.
     * @param SummitOrder $order
     * @param string $set_password_link
     */
    public function __construct(SummitOrder $order, string $set_password_link)
    {
        parent::__construct($order);
        // need to add the dashboard client id and return url
        $base_url = Config::get("registration.dashboard_base_url", null);
        if(empty($base_url))
            throw new \InvalidArgumentException("missing dashboard_base_url value");

        $back_url = Config::get("registration.dashboard_back_url", null);
        if(empty($back_url))
            throw new \InvalidArgumentException("missing dashboard_back_url value");

        $this->set_password_link = sprintf(
            "%s?client_id=%s&redirect_uri=%s",
            $set_password_link,
            Config::get("registration.dashboard_client_id"),
            urlencode(sprintf($back_url, $base_url))
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $subject = sprintf('[%1$s] Order Confirmation for %1$s', $this->summit_name);
        $from    = $this->support_email;

        $mail = $this->from($from)
            ->to($this->owner_email)
            ->subject($subject)
            ->view('emails.registration.unregistered_member_order_paid');

        /*
        foreach( $this->ticket_pdf_contents as $ticket_number => $pdf_content) {
            if (!empty($pdf_content)) {
                $mail = $mail->attachData(base64_decode($pdf_content), 'ticket_' . $ticket_number . '.pdf', [
                    'mime' => 'application/pdf',
                ]);
            }
        }
        */

        return $mail;

    }
}
