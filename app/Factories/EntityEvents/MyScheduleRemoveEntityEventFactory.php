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
use App\Events\MyScheduleRemove;
use models\summit\SummitEntityEvent;
/**
 * Class MyScheduleRemoveEntityEventFactory
 * @package App\Factories\EntityEvents
 */
final class MyScheduleRemoveEntityEventFactory
{
    /**
     * @param MyScheduleRemove $event
     * @return SummitEntityEvent
     */
    public static function build(MyScheduleRemove $event){
        $entity_event = new SummitEntityEvent;
        $entity_event->setEntityClassName('MySchedule');
        $entity_event->setEntityId($event->getEventId());
        $entity_event->setType('DELETE');
        $entity_event->setOwner($event->getMember());
        $entity_event->setSummit($event->getSummit());
        $entity_event->setMetadata('');
        return $entity_event;
    }
}