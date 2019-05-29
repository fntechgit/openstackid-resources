<?php namespace App\ModelSerializers\Locations;
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

use models\summit\SummitBookableVenueRoomAttributeValue;
use ModelSerializers\SerializerRegistry;
use ModelSerializers\SilverStripeSerializer;
/**
 * Class SummitBookableVenueRoomAttributeValueSerializer
 * @package App\ModelSerializers\Locations
 */
class SummitBookableVenueRoomAttributeValueSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [
        'Value'  => 'value:json_string',
        'TypeId' => 'type_id:json_int',
    ];

    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array() )
    {
        $attr_value   = $this->object;
        if(!$attr_value instanceof SummitBookableVenueRoomAttributeValue)
            return [];

        $values = parent::serialize($expand, $fields, $relations, $params);

        if (!empty($expand)) {
            $exp_expand = explode(',', $expand);
            foreach ($exp_expand as $relation) {
                switch (trim($relation)) {
                    case 'attribute_type': {
                        unset($values['type_id']);
                        $values['type'] = SerializerRegistry::getInstance()->getSerializer($attr_value->getType())->serialize();
                    }
                    break;

                }
            }
        }
        return $values;
    }
}