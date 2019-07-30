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
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use models\summit\Summit;
/**
 * Class ExternalIngestionResultEmail
 * @package App\Mail\Registration\ExternalIngestion
 */
abstract class ExternalIngestionResultEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $summit_name;

    /**
     * @var int
     */
    public $summit_id;

    /**
     * @var string
     */
    public $email_to;

    /**
     * ExternalIngestionResultEmail constructor.
     * @param string $email_to
     * @param Summit $summit
     */
    public function __construct(string $email_to, Summit $summit)
    {
        $this->email_to    = $email_to;
        $this->summit_id   = $summit->getId();
        $this->summit_name = $summit->getName();
    }

}