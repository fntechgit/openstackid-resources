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
use models\summit\Summit;
use models\summit\SummitTicketType;
/**
 * Class SummitTicketTypeFactory
 * @package App\Models\Foundation\Summit\Factories
 */
final class SummitTicketTypeFactory
{
    /**
     * @param array $data
     * @return SummitTicketType
     */
    public static function build(Summit $summit,array $data){
        $ticket_type = new SummitTicketType;
        $ticket_type->setSummit($summit);
        return self::populate($ticket_type, $data);
    }

    /**
     * @param SummitTicketType $ticket_type
     * @param array $data
     * @return SummitTicketType
     */
    public static function populate(SummitTicketType $ticket_type, array $data){

        if(isset($data['name']))
            $ticket_type->setName(trim($data['name']));

        if(isset($data['description']))
            $ticket_type->setDescription(trim($data['description']));

        if(isset($data['external_id']))
            $ticket_type->setExternalId(trim($data['external_id']));

        if(isset($data['cost']))
            $ticket_type->setCost(floatval($data['cost']));

        if(isset($data['currency']))
            $ticket_type->setCurrency(trim($data['currency']));

        if(isset($data['quantity_2_sell']))
            $ticket_type->setQuantity2Sell(intval($data['quantity_2_sell']));

        if(isset($data['max_quantity_per_order']))
            $ticket_type->setMaxQuantityPerOrder(intval($data['max_quantity_per_order']));

        if(isset($data['sales_start_date'])){
            $val = intval($data['sales_start_date']);
            $val = new \DateTime("@$val");
            $val->setTimezone($ticket_type->getSummit()->getTimeZone());
            $ticket_type->setSalesStartDate($ticket_type->getSummit()->convertDateFromTimeZone2UTC($val));
        }

        if(isset($data['sales_end_date'])){
            $val = intval($data['sales_end_date']);
            $val = new \DateTime("@$val");
            $val->setTimezone($ticket_type->getSummit()->getTimeZone());
            $ticket_type->setSalesEndDate($ticket_type->getSummit()->convertDateFromTimeZone2UTC($val));
        }

        if(isset($data['badge_type']))
            $ticket_type->setBadgeType($data['badge_type']);

        return $ticket_type;
    }
}