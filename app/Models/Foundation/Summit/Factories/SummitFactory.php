<?php namespace App\Models\Foundation\Summit\Factories;
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

use models\exceptions\ValidationException;
use models\summit\Summit;
/**
 * Class SummitFactory
 * @package App\Models\Foundation\Summit\Factories
 */
final class SummitFactory
{
    /**
     * @param array $data
     * @return Summit
     */
    public static function build(array $data){
        return self::populate(new Summit, $data);
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @return Summit
     */
    public static function populate(Summit $summit, array $data){

        if(isset($data['name']) ){
            $summit->setName(trim($data['name']));
        }

        if(isset($data['time_zone_id']) ){
            $summit->setTimeZoneId(trim($data['time_zone_id']));
        }

        if(isset($data['max_submission_allowed_per_user']) ){
            $summit->setMaxSubmissionAllowedPerUser(intval($data['max_submission_allowed_per_user']));
        }

        if(isset($data['active']) ){
            $summit->setActive(boolval($data['active']));
        }

        if(isset($data['available_on_api']) ){
            $summit->setAvailableOnApi(boolval($data['available_on_api']));
        }

        if(isset($data['dates_label']) ){
            $summit->setDatesLabel(trim($data['dates_label']));
        }

        if(isset($data['calendar_sync_name']) ){
            $summit->setCalendarSyncName(trim($data['calendar_sync_name']));
        }

        if(isset($data['calendar_sync_desc']) ){
            $summit->setCalendarSyncDesc(trim($data['calendar_sync_desc']));
        }

        if(array_key_exists('begin_allow_booking_date', $data) && array_key_exists('end_allow_booking_date', $data)) {
            if (isset($data['begin_allow_booking_date']) && isset($data['end_allow_booking_date'])) {
                $start_datetime = intval($data['begin_allow_booking_date']);
                $start_datetime = new \DateTime("@$start_datetime");
                $start_datetime->setTimezone($summit->getTimeZone());
                $end_datetime = intval($data['end_allow_booking_date']);
                $end_datetime = new \DateTime("@$end_datetime");
                $end_datetime->setTimezone($summit->getTimeZone());
                // set local time from UTC
                $summit->setBeginAllowBookingDate($start_datetime);
                $summit->setEndAllowBookingDate($end_datetime);
            }
            else{
                $summit->clearAllowBookingDates();
            }
        }

        if(array_key_exists('start_date', $data) && array_key_exists('end_date', $data)) {
            if (isset($data['start_date']) && isset($data['end_date'])) {
                $start_datetime = intval($data['start_date']);
                $start_datetime = new \DateTime("@$start_datetime");
                $start_datetime->setTimezone($summit->getTimeZone());
                $end_datetime = intval($data['end_date']);
                $end_datetime = new \DateTime("@$end_datetime");
                $end_datetime->setTimezone($summit->getTimeZone());

                // set local time from UTC
                $summit->setBeginDate($start_datetime);
                $summit->setEndDate($end_datetime);
            }
            else{
                $summit->clearBeginEndDates();
            }
        }

        if(array_key_exists('registration_begin_date', $data) && array_key_exists('registration_end_date', $data)) {
            if (isset($data['registration_begin_date']) && isset($data['registration_end_date'])) {
                $start_datetime = intval($data['registration_begin_date']);
                $start_datetime = new \DateTime("@$start_datetime");
                $start_datetime->setTimezone($summit->getTimeZone());
                $end_datetime = intval($data['registration_end_date']);
                $end_datetime = new \DateTime("@$end_datetime");
                $end_datetime->setTimezone($summit->getTimeZone());

                // set local time from UTC
                $summit->setRegistrationBeginDate($start_datetime);
                $summit->setRegistrationEndDate($end_datetime);

                $summit_begin_date = $summit->getLocalBeginDate();
                $summit_end_date   = $summit->getLocalEndDate();
                if(!is_null($summit_begin_date) && !is_null($summit_end_date)){
                    if($start_datetime > $summit_end_date)
                        throw new ValidationException("The Registration Begin Date cannot be after the Summit End Date.");

                    if($end_datetime > $summit_end_date)
                        throw new ValidationException("The Registration End Date cannot be after the Summit End Date.");
                }

            }
            else{
                $summit->clearRegistrationDates();
            }
        }

        if(array_key_exists('start_showing_venues_date', $data)){
            if (isset($data['start_showing_venues_date'])) {
                $start_datetime = intval($data['start_showing_venues_date']);
                $start_datetime = new \DateTime("@$start_datetime");
                $start_datetime->setTimezone($summit->getTimeZone());

                // set local time from UTC
                $summit->setStartShowingVenuesDate($start_datetime);
            }
            else{
                $summit->clearStartShowingVenuesDate();
            }
        }

        if(array_key_exists('reassign_ticket_till_date', $data)){
            if (isset($data['reassign_ticket_till_date'])) {
                $date = intval($data['reassign_ticket_till_date']);
                $date = new \DateTime("@$date");
                $date->setTimezone($summit->getTimeZone());

                // set local time from UTC
                $summit->setReassignTicketTillDate($date);
            }
            else{
                $summit->clearReassignTicketTillDate();
            }
        }

        if(array_key_exists('schedule_start_date', $data)) {
            if (isset($data['schedule_start_date'])) {
                $start_datetime = intval($data['schedule_start_date']);
                $start_datetime = new \DateTime("@$start_datetime");
                $start_datetime->setTimezone($summit->getTimeZone());

                // set local time from UTC
                $summit->setScheduleDefaultStartDate($start_datetime);
            }
            else{
                $summit->clearScheduleDefaultStartDate();
            }
        }

        if(isset($data['link']) ){
            $summit->setLink(trim($data['link']));
        }

        if(isset($data['registration_disclaimer_mandatory']) ){
            $registration_disclaimer_mandatory = boolval($data['registration_disclaimer_mandatory']);
            $summit->setRegistrationDisclaimerMandatory($registration_disclaimer_mandatory);
            if($registration_disclaimer_mandatory){

                $registration_disclaimer_content = $data['registration_disclaimer_content'] ?? '';
                if(empty($registration_disclaimer_content)){
                    throw new ValidationException("registration_disclaimer_content is mandatory");
                }
            }
        }

        if(isset($data['registration_disclaimer_content'])){
            $summit->setRegistrationDisclaimerContent(trim($data['registration_disclaimer_content']));
        }

        if(isset($data['link']) ){
            $summit->setLink(trim($data['link']));
        }

        if(isset($data['slug']) ){
            $summit->setRawSlug(trim($data['slug']));
        }

        if(isset($data['secondary_registration_link']) ){
            $summit->setSecondaryRegistrationLink(trim($data['secondary_registration_link']));
        }

        if(isset($data['secondary_registration_label']) ){
            $summit->setSecondaryRegistrationLabel(trim($data['secondary_registration_label']));
        }

        if(isset($data['meeting_room_booking_start_time']) ){
            // no need to convert to UTC, its only relative time
            $meeting_room_booking_start_time = intval($data['meeting_room_booking_start_time']);
            $meeting_room_booking_start_time = new \DateTime("@$meeting_room_booking_start_time");
            $summit->setMeetingRoomBookingStartTime($meeting_room_booking_start_time);
        }

        if(isset($data['meeting_room_booking_end_time']) ){
            // no need to convert to UTC, its only relative time
            $meeting_room_booking_end_time = intval($data['meeting_room_booking_end_time']);
            $meeting_room_booking_end_time = new \DateTime("@$meeting_room_booking_end_time");
            $summit->setMeetingRoomBookingEndTime($meeting_room_booking_end_time);
        }

        if(isset($data['meeting_room_booking_slot_length']) ){
            // minutes
            $summit->setMeetingRoomBookingSlotLength(intval($data['meeting_room_booking_slot_length']));
        }

        if(isset($data['registration_reminder_email_days_interval']) ){
            // days
            $summit->setRegistrationReminderEmailDaysInterval(intval($data['registration_reminder_email_days_interval']));
        }

        if(isset($data['meeting_room_booking_max_allowed']) ){
            // maximun books per user
            $summit->setMeetingRoomBookingMaxAllowed(intval($data['meeting_room_booking_max_allowed']));
        }

        // external schedule feed

        if(isset($data['api_feed_type'])){
            $summit->setApiFeedType($data['api_feed_type']);
        }

        if(isset($data['api_feed_url'])){
            $summit->setApiFeedUrl(trim($data['api_feed_url']));
        }

        if(isset($data['api_feed_key'])){
            $summit->setApiFeedKey(trim($data['api_feed_key']));
        }

        // external registration feed

        if(isset($data['external_summit_id']) ){
            $summit->setExternalSummitId(trim($data['external_summit_id']));
        }

        if(isset($data['external_registration_feed_type']) ){
            $summit->setExternalRegistrationFeedType(trim($data['external_registration_feed_type']));
        }

        if(isset($data['external_registration_feed_api_key']) ){
            $summit->setExternalRegistrationFeedApiKey(trim($data['external_registration_feed_api_key']));
        }

        $summit->generateRegistrationSlugPrefix();

        return $summit;
    }
}