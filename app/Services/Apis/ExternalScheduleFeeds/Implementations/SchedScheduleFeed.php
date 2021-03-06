<?php namespace App\Services\Apis\ExternalScheduleFeeds;
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
use DateTime;
use Illuminate\Support\Facades\Log;
/**
 * Class SchedScheduleFeed
 * @package App\Services\Apis\ExternalScheduleFeeds
 */
final class SchedScheduleFeed  extends AbstractExternalScheduleFeed
{

    /**
     * @return array
     * @throws \Exception
     */
    public function getEvents(): array
    {

        $apiFeedUrl = $this->summit->getApiFeedUrl();
        $apiFeedKey = $this->summit->getApiFeedKey();

        if(empty($apiFeedUrl)) {
            Log::warning(sprintf("api_feeed_url is empty for summit %s", $this->summit->getId()));
            return [];
        }
        if(empty($apiFeedKey)) {
            Log::warning(sprintf("api_feeed_key is empty for summit %s", $this->summit->getId()));
            return [];
        }
        $url = sprintf("%s/api/session/list?api_key=%s&format=json", $apiFeedUrl, $apiFeedKey);
        $response = $this->get($url);
        $timezone = $this->summit->getTimeZone();
        $events   = [];
        foreach($response as $event){
            // Y-m-d H:i:s
            $event_start = DateTime::createFromFormat('Y-m-d H:i:s', $event['event_start'], $timezone);
            // Y-m-d H:i:s
            $event_end =  DateTime::createFromFormat('Y-m-d H:i:s', $event['event_end'], $timezone);
            $speakers  = [];
            if(isset($event['speakers'])){
                foreach(explode(',', trim($event['speakers'])) as $speaker_full_name){
                    $speakers[] = trim($speaker_full_name);;
                }
            }

            $events[] = [
                'external_id' => trim($event['event_key']),
                'title'       => isset($event['name']) ? trim(AbstractExternalScheduleFeed::convert2UTF8($event['name'])) : '',
                'abstract'    => isset($event['description']) ? trim(AbstractExternalScheduleFeed::convert2UTF8($event['description'])) : '',
                'track'       => isset($event['event_type']) ? trim(AbstractExternalScheduleFeed::convert2UTF8($event['event_type'])) : '',
                'location'    => isset($event['venue']) ? trim(AbstractExternalScheduleFeed::convert2UTF8($event['venue'])) : '',
                "start_date"  => $event_start->getTimestamp(),
                "end_date"    => $event_end->getTimestamp(),
                'speakers'    => $speakers
            ];
        }
        return $events;

    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getSpeakers(): array
    {
        $apiFeedUrl = $this->summit->getApiFeedUrl();
        $apiFeedKey = $this->summit->getApiFeedKey();

        if(empty($apiFeedUrl)) {
            Log::warning(sprintf("api_feeed_url is empty for summit %s", $this->summit->getId()));
            return [];
        }
        if(empty($apiFeedKey)) {
            Log::warning(sprintf("api_feeed_key is empty for summit %s", $this->summit->getId()));
            return [];
        }

        $url = sprintf("%s/api/user/list?api_key=%s&format=json", $apiFeedUrl, $apiFeedKey);
        $response = $this->get($url);

        $speakers = [];
        foreach($response as $speaker){
            if(!isset($speaker['name'])) continue;
            $speakerFullNameParts = explode(" ", AbstractExternalScheduleFeed::convert2UTF8($speaker['name']));
            $speakerLastName    = trim(trim(array_pop($speakerFullNameParts)));
            $speakerFirstName   = trim(implode(" ", $speakerFullNameParts));
            $email              = isset($speaker['email']) ? trim(AbstractExternalScheduleFeed::convert2UTF8($speaker['email'])) : null;

            if(empty($email))
                $email = $this->getDefaultSpeakerEmail(trim(AbstractExternalScheduleFeed::convert2UTF8($speaker['name'])));

            $speakers[trim(AbstractExternalScheduleFeed::convert2UTF8($speaker['name']))] = [
                'full_name'  => trim(AbstractExternalScheduleFeed::convert2UTF8($speaker['name'])),
                'first_name' => trim(AbstractExternalScheduleFeed::convert2UTF8($speakerFirstName)),
                'last_name'  => trim(AbstractExternalScheduleFeed::convert2UTF8($speakerLastName)),
                'email'      => AbstractExternalScheduleFeed::convert2UTF8($email),
                'company'    => trim(AbstractExternalScheduleFeed::convert2UTF8($speaker['company'])),
                'position'   => trim(AbstractExternalScheduleFeed::convert2UTF8($speaker['position'])),
                'avatar'     => trim(AbstractExternalScheduleFeed::convert2UTF8($speaker['avatar'])),
            ];
        }

        return $speakers;
    }

    public function getDefaultSpeakerEmail(string $speakerFullName): string
    {
        return sprintf("%s@sched.com",  ltrim(str_replace(",", "",str_replace(" ", "",strtolower($speakerFullName)))), ".");
    }
}