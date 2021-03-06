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
use App\Models\Foundation\Summit\Speakers\SpeakerEditPermissionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
/**
 * Class SpeakerEditPermissionRequested
 * @package App\Mail
 */
final class SpeakerEditPermissionRequestedEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $requested_by_full_name;

    /**
     * @var string
     */
    public $speaker_full_name;

    /**
     * @var string
     */
    public $link;

    /**
     * SpeakerEditPermissionRequested constructor.
     * @param SpeakerEditPermissionRequest $request
     * @param string $token
     */
    public function __construct(SpeakerEditPermissionRequest $request, string $token)
    {

        $this->requested_by_full_name = $request->getRequestedBy()->getFullName();
        $this->speaker_full_name =  $request->getSpeaker()->getFullName();
        $this->token             = $token;
        $this->link              = $request->getConfirmationLink($request->getSpeaker()->getId(), $token);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from('noreply@openstack.org')
            ->subject("OpenStack - Speaker Profile Edit Permission Requested")
            ->view('emails.speakers.permissioneditrequested');
    }
}
