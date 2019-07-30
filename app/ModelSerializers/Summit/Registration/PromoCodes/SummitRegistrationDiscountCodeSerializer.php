<?php namespace ModelSerializers;
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
use models\summit\SummitRegistrationDiscountCode;
/**
 * Class SummitRegistrationDiscountCodeSerializer
 * @package ModelSerializers
 */
class SummitRegistrationDiscountCodeSerializer extends SummitRegistrationPromoCodeSerializer
{
    protected static $array_mappings = [
        'Rate'   => 'rate:json_float',
        'Amount' => 'amount:json_float',
    ];

    protected static $allowed_relations = [
        'ticket_types_rules',
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
        if(!count($relations)) $relations = $this->getAllowedRelations();

        $code            = $this->object;
        if(!$code instanceof SummitRegistrationDiscountCode) return [];
        $values          = parent::serialize($expand, $fields, $relations, $params);

        unset($values['allowed_ticket_types']);

        if(in_array('ticket_types_rules', $relations)) {
            $ticket_types = [];
            foreach ($code->getTicketTypesRules() as $ticket_type) {
                $ticket_types[] = $ticket_type->getId();
            }
            $values['ticket_types_rules'] = $ticket_types;
        }

        if (!empty($expand)) {
            foreach (explode(',', $expand) as $relation) {
                switch (trim($relation)) {
                  case 'ticket_types_rules': {
                        unset($values['ticket_types_rules']);
                        $ticket_types = [];
                        foreach ($code->getTicketTypesRules() as $ticket_type) {
                            $ticket_types[] = SerializerRegistry::getInstance()->getSerializer($ticket_type)->serialize($expand);
                        }
                        $values['ticket_types_rules'] = $ticket_types;
                    }
                        break;

                }
            }
        }

        return $values;
    }
}