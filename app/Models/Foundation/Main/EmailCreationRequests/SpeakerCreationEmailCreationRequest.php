<?php namespace models\main;
/**
 * Copyright 2017 OpenStack Foundation
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
use Doctrine\ORM\Mapping AS ORM;
use models\summit\PresentationSpeaker;
/**
 * @ORM\Entity
 * @ORM\Table(name="SpeakerCreationEmailCreationRequest")
 * Class SpeakerCreationEmailCreationRequest
 * @package models\main
 */
class SpeakerCreationEmailCreationRequest extends EmailCreationRequest
{
    /**
     * @ORM\ManyToOne(targetEntity="models\summit\PresentationSpeaker")
     * @ORM\JoinColumn(name="SpeakerID", referencedColumnName="ID")
     * @var PresentationSpeaker
     */
    protected $speaker;

    public function __construct()
    {
        $this->template_name = "presentation-speaker-creation";
        parent::__construct();
    }

    /**
     * @return PresentationSpeaker
     */
    public function getSpeaker()
    {
        return $this->speaker;
    }

    /**
     * @param PresentationSpeaker $speaker
     */
    public function setSpeaker($speaker)
    {
        $this->speaker = $speaker;
    }
}