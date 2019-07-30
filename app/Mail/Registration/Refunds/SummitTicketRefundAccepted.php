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
/**
 * Class SummitTicketRefundAccepted
 * @package App\Mail
 */
class SummitTicketRefundAccepted extends SummitTicketRefundRequestOwner
{
    public $tries = 1;

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $subject = sprintf('[%1$s] Your ticket refund request for %1$s has been accepted', $this->summit_name);

        $mail = $this->from($this->support_email)
            ->to($this->owner_email)
            ->subject($subject)
            ->view('emails.registration.ticket_refund_accepted');

        return $mail;
    }
}
