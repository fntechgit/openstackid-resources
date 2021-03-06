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
use Illuminate\Support\Facades\Config;
use models\summit\PresentationSpeaker;

/**
 * Class PresentationSpeakerSerializer
 * @package ModelSerializers
 */
class PresentationSpeakerSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [
        'FirstName'               => 'first_name:json_string',
        'LastName'                => 'last_name:json_string',
        'Title'                   => 'title:json_string',
        'Bio'                     => 'bio:json_string',
        'IRCHandle'               => 'irc:json_string',
        'TwitterName'             => 'twitter:json_string',
        'OrgHasCloud'             => 'org_has_cloud:json_boolean',
        'Country'                 => 'country:json_string',
        'AvailableForBureau'      => 'available_for_bureau:json_boolean',
        'FundedTravel'            => 'funded_travel:json_boolean',
        'WillingToTravel'         => 'willing_to_travel:json_boolean',
        'WillingToPresentVideo'   => 'willing_to_present_video:json_boolean',
        'Email'                   => 'email:json_obfuscated_email',
        'MemberID'                => 'member_id:json_int',
        'RegistrationRequestId'   => 'registration_request_id:json_int',
        'ProfilePhotoUrl'         => 'pic:json_url'
    ];

    protected static $allowed_relations = [
        'member',
    ];

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = [], array $relations = [], array $params = [] )
    {
        if(!count($relations)) $relations  = $this->getAllowedRelations();

        $speaker                           = $this->object;
        if(!$speaker instanceof PresentationSpeaker) return [];

        $values                            = parent::serialize($expand, $fields, $relations, $params);
        $summit_id                         = isset($params['summit_id'])? intval($params['summit_id']):null;
        $published                         = isset($params['published'])? intval($params['published']):true;
        if(!is_null($summit_id)) {
            $values['presentations']           = $speaker->getPresentationIds($summit_id, $published);
            $values['moderated_presentations'] = $speaker->getModeratedPresentationIds($summit_id, $published);
        }

        if (in_array('member', $relations) && $speaker->hasMember())
        {
            $member              = $speaker->getMember();
            $values['gender']    = $member->getGender();
            $values['member_id'] = intval($member->getId());
        }

        if(empty($values['first_name']) || empty($values['last_name'])){

            $first_name = '';
            $last_name  = '';
            if ($speaker->hasMember())
            {
                $member     = $speaker->getMember();
                $first_name = $member->getFirstName();
                $last_name  = $member->getLastName();
            }
            $values['first_name'] = $first_name;
            $values['last_name']  = $last_name;
        }

        $affiliations = [];
        if($speaker->hasMember()) {
            $member = $speaker->getMember();
            foreach ($member->getCurrentAffiliations() as $affiliation) {
                $affiliations[] = SerializerRegistry::getInstance()->getSerializer($affiliation)->serialize('organization');
            }
        }
        $values['affiliations'] = $affiliations;

        $languages = [];
        foreach ($speaker->getLanguages() as $language){
            $languages[] = SerializerRegistry::getInstance()->getSerializer($language)->serialize();
        }
        $values['languages'] = $languages;

        $other_presentation_links = [];
        foreach ($speaker->getOtherPresentationLinks() as $link){
            $other_presentation_links[] = SerializerRegistry::getInstance()->getSerializer($link)->serialize();
        }
        $values['other_presentation_links'] = $other_presentation_links;

        $areas_of_expertise = [];
        foreach ($speaker->getAreasOfExpertise() as $exp){
            $areas_of_expertise[] = SerializerRegistry::getInstance()->getSerializer($exp)->serialize();
        }
        $values['areas_of_expertise'] = $areas_of_expertise;

        $travel_preferences = [];
        foreach ($speaker->getTravelPreferences() as $tp){
            $travel_preferences[] = SerializerRegistry::getInstance()->getSerializer($tp)->serialize();
        }
        $values['travel_preferences'] = $travel_preferences;

        $active_involvements = [];
        foreach ($speaker->getActiveInvolvements() as $ai){
            $active_involvements[] = SerializerRegistry::getInstance()->getSerializer($ai)->serialize();
        }
        $values['active_involvements'] = $active_involvements;

        $organizational_roles = [];
        foreach ($speaker->getOrganizationalRoles() as $or){
            $organizational_roles[] = SerializerRegistry::getInstance()->getSerializer($or)->serialize();
        }
        $values['organizational_roles'] = $organizational_roles;

        if (!empty($expand)) {
            foreach (explode(',', $expand) as $relation) {
                switch (trim($relation)) {
                    case 'presentations': {
                        $presentations = [];
                        foreach ($speaker->getPresentations($summit_id, $published) as $p) {
                            $presentations[] = SerializerRegistry::getInstance()->getSerializer($p)->serialize();
                        }
                        $values['presentations'] = $presentations;

                        $moderated_presentations = [];
                        foreach ($speaker->getModeratedPresentations($summit_id, $published) as $p) {
                            $moderated_presentations[] = SerializerRegistry::getInstance()->getSerializer($p)->serialize();
                        }
                        $values['moderated_presentations'] = $moderated_presentations;
                    }
                    break;
                    case 'member': {
                       if($speaker->hasMember()){
                           unset($values['member_id']);
                           $values['member'] =  SerializerRegistry::getInstance()->getSerializer($speaker->getMember())->serialize();
                       }
                    }
                    break;
                }
            }
        }

        return $values;
    }
}