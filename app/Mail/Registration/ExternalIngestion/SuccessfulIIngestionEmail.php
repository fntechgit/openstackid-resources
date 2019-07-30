<?php namespace App\Mail\Registration\ExternalIngestion;
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
use models\summit\Summit;
/**
 * Class SuccessfulIIngestionEmail
 * @package App\Mail\Registration\ExternalIngestion
 */
class SuccessfulIIngestionEmail extends ExternalIngestionResultEmail
{

    /**
     * @var string
     */
    public $feed_type;

    /**
     * @var string
     */
    public $external_id;

    /**
     * SuccessfulIIngestionEmail constructor.
     * @param string $email_to
     * @param Summit $summit
     */
    public function __construct(string $email_to, Summit $summit)
    {
        parent::__construct($email_to, $summit);
        $this->feed_type   = $summit->getExternalRegistrationFeedType();
        $this->external_id = $summit->getExternalSummitId();
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = sprintf("[%s] - External Registration Data Ingestion", $this->summit_name);

        $from = Config::get("mail.from");
        if(empty($from)){
            throw new \InvalidArgumentException("mail.from is not set");
        }

        $mail = $this->from($from)
            ->to($this->email_to)
            ->subject($subject)
            ->view('emails.registration.external_ingestion_successful');

        return $mail;
    }
}