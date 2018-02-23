<?php namespace App\Factories\EntityEvents;
/**
 * Copyright 2018 OpenStack Foundation
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
use App\Events\TrackUpdated;
use Illuminate\Support\Facades\App;
use models\summit\SummitEntityEvent;

/**
 * Class TrackUpdatedEntityEventFactory
 * @package App\Factories\EntityEvents
 */
final class TrackUpdatedEntityEventFactory
{
    /**
     * @param TrackUpdated $event
     * @return SummitEntityEvent[]
     */
    public static function build(TrackUpdated $event){
        $list                            = [];
        $resource_server_context         = App::make(\models\oauth2\IResourceServerContext::class);
        $member_repository               = App::make(\models\main\IMemberRepository::class);
        $owner_id                        = $resource_server_context->getCurrentUserExternalId();
        if(is_null($owner_id)) $owner_id = 0;

        foreach($event->getTrack()->getRelatedPublishedSummitEvents() as $summit_event) {

            $entity_event = new SummitEntityEvent;
            $entity_event->setEntityClassName($summit_event->getClassName());
            $entity_event->setEntityId($summit_event->getId());
            $entity_event->setType('UPDATE');

            if ($owner_id > 0) {
                $member = $member_repository->getById($owner_id);
                $entity_event->setOwner($member);
            }

            $entity_event->setSummit($summit_event->getSummit());
            $entity_event->setMetadata('');

            $list[]  = $entity_event;
        }

        return $list;
    }
}