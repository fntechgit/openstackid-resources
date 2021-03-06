<?php namespace ModelSerializers;
/**
 * Copyright 2016 OpenStack Foundation
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
use Libs\ModelSerializers\AbstractSerializer;
use models\summit\SummitAttendee;
/**
 * Class SummitAttendeeSerializer
 * @package ModelSerializers
 */
final class SummitAttendeeSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [
        'SummitHallCheckedIn'     => 'summit_hall_checked_in:json_boolean',
        'SummitHallCheckedInDate' => 'summit_hall_checked_in_date:datetime_epoch',
        'SharedContactInfo'       => 'shared_contact_info:json_boolean',
        'MemberId'                => 'member_id:json_int',
        'SummitId'                => 'summit_id:json_int',
        'FirstName'               => 'first_name:json_string',
        'Surname'                 => 'last_name:json_string',
        'Email'                   => 'email:json_string',
        'CompanyName'             => 'company:json_string',
        'DisclaimerAcceptedDate'  => 'disclaimer_accepted_date:datetime_epoch',
        'Status'                  => 'status:json_string'
    ];

    protected static $allowed_relations = [
        'extra_questions',
        'tickets',
    ];

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array())
    {
        if(!count($relations)) $relations = $this->getAllowedRelations();
        $attendee = $this->object;
        if(!$attendee instanceof SummitAttendee) return [];
        $serializer_type = SerializerRegistry::SerializerType_Public;

        if(isset($params['serializer_type']))
            $serializer_type = $params['serializer_type'];
        $summit         = $attendee->getSummit();

        $attendee->updateStatus();

        $values         = parent::serialize($expand, $fields, $relations, $params);
        $member         = null;
        $speaker        = null;

        if (in_array('tickets', $relations)) {
            $tickets = [];
            foreach ($attendee->getTickets() as $t) {
                if (!$t->hasTicketType()) continue;
                if ($t->isCancelled()) continue;
                $tickets[] = intval($t->getTicketType()->getId());
            }
            $values['tickets'] = $tickets;
        }

        if (in_array('extra_questions', $relations)) {
            $extra_question_answers = [];

            foreach ($attendee->getExtraQuestionAnswers() as $answer) {
                $extra_question_answers[] = $answer->getId();
            }
            $values['extra_questions'] = $extra_question_answers;
        }

        if($attendee->hasMember())
        {
            $member               = $attendee->getMember();
            $values['member_id']  = $member->getId();
            $speaker              = $summit->getSpeakerByMember($member);
            if (!is_null($speaker)) {
                $values['speaker_id'] = intval($speaker->getId());
            }
        }

        if (!empty($expand)) {
            $exp_expand = explode(',', $expand);
            foreach ($exp_expand as $relation) {
                switch (trim($relation)) {
                    case 'tickets': {
                        if (!in_array('tickets', $relations)) break;
                        unset($values['tickets']);
                        $tickets = [];
                        foreach($attendee->getTickets() as $t)
                        {
                            if (!$t->hasTicketType()) continue;
                            if ($t->isCancelled()) continue;
                            $tickets[] = SerializerRegistry::getInstance()->getSerializer($t)->serialize(AbstractSerializer::getExpandForPrefix('tickets', $expand));
                        }
                        $values['tickets'] = $tickets;
                    }
                    break;
                    case 'extra_questions': {
                        if (!in_array('extra_questions', $relations)) break;
                        unset($values['extra_questions']);
                        $extra_question_answers = [];
                        foreach($attendee->getExtraQuestionAnswers() as $answer)
                        {
                            $extra_question_answers[] = SerializerRegistry::getInstance()->getSerializer($answer)->serialize(AbstractSerializer::getExpandForPrefix('extra_questions', $expand));
                        }
                        $values['extra_questions'] = $extra_question_answers;
                    }
                        break;
                    case 'speaker': {
                        if (!is_null($speaker))
                        {
                            unset($values['speaker_id']);
                            $values['speaker'] = SerializerRegistry::getInstance()->getSerializer($speaker)->serialize(AbstractSerializer::getExpandForPrefix('speaker', $expand));
                        }
                    }
                    break;
                    case 'member':{
                        if($attendee->hasMember())
                        {
                            unset($values['member_id']);
                            $values['member']    = SerializerRegistry::getInstance()
                                ->getSerializer($attendee->getMember(), $serializer_type)
                                ->serialize(
                                    AbstractSerializer::getExpandForPrefix('member', $expand),
                                    [],
                                    [],
                                    ['summit' => $attendee->getSummit()]);
                        }
                    }
                    break;
                }
            }
        }

        return $values;
    }
}