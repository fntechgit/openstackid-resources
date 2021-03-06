<?php namespace utils;
use models\exceptions\ValidationException;

/**
 * Copyright 2015 OpenStack Foundation
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
final class FilterParser
{
    /**
     * @param mixed $filters
     * @param array $allowed_fields
     * @throws FilterParserException
     * @return Filter
     */
    public static function parse($filters, $allowed_fields = array())
    {
        $res                 = [];
        $matches             = [];
        $and_fields          = [];

        if (!is_array($filters))
            $filters = array($filters);

        foreach ($filters as $filter) // parse AND filters
        {

            $f = null;
            // parse OR filters
            $or_filters = preg_split("|(?<!\\\),|", $filter);


            if (count($or_filters) > 1) {
                $f = [];
                foreach ($or_filters as $of) {

                    //single filter
                    if(empty($of)) continue;

                    preg_match('/[=<>][=>@]{0,1}/', $of, $matches);

                    if (count($matches) != 1)
                        throw new FilterParserException(sprintf("invalid OR filter format %s (should be [:FIELD_NAME:OPERAND:VALUE])", $of));

                    $op       = $matches[0];
                    $operands = explode($op, $of);
                    $field    = $operands[0];
                    $value    = $operands[1];

                    if (!isset($allowed_fields[$field])){
                        throw new FilterParserException(sprintf("filter by field %s is not allowed", $field));
                    }
                    if (!in_array($op, $allowed_fields[$field])){
                        throw new FilterParserException(sprintf("%s op is not allowed for filter by field %s",$op, $field));
                    }

                    $f_or = self::buildFilter($field, $op, $value);
                    if (!is_null($f_or))
                        $f[] = $f_or;
                }
            } else {
                //single filter
                preg_match('/[=<>][=>@]{0,1}/', $filter, $matches);

                if (count($matches) != 1)
                    throw new FilterParserException(sprintf("invalid filter format %s (should be [:FIELD_NAME:OPERAND:VALUE])", $filter));

                $op       = $matches[0];
                $operands = explode($op, $filter);
                $field    = $operands[0];
                $value    = $operands[1];

                // parse AND on same fields
                $values = explode('&&', $value);
                if (count($values) > 1) {
                    $value = $values;
                }

                if (!isset($allowed_fields[$field])){
                    throw new FilterParserException(sprintf("filter by field %s is not allowed", $field));
                }
                if (!in_array($op, $allowed_fields[$field])){
                    throw new FilterParserException(sprintf("%s op is not allowed for filter by field %s",$op, $field));

                }
                if(in_array($field, $and_fields))
                    throw new FilterParserException(sprintf("filter by field %s is already on an and expression", $field));

                $and_fields[] = $field;

                $f = self::buildFilter($field, $op, $value);
            }

            if (!is_null($f))
                $res[] = $f;
        }
        return new Filter($res);
    }

    /**
     * Factory Method
     *
     * @param string $field
     * @param string $op
     * @param string $value
     * @return FilterElement|null
     */
    public static function buildFilter($field, $op, $value)
    {
        switch ($op) {
            case '==':
                return FilterElement::makeEqual($field, $value);
                break;
            case '=@':
                return FilterElement::makeLike($field, $value);
                break;
            case '>':
                return FilterElement::makeGreather($field, $value);
                break;
            case '>=':
                return FilterElement::makeGreatherOrEqual($field, $value);
                break;
            case '<':
                return FilterElement::makeLower($field, $value);
                break;
            case '<=':
                return FilterElement::makeLowerOrEqual($field, $value);
                break;
            case '<>':
                return FilterElement::makeNotEqual($field, $value);
                break;
        }
        return null;
    }
}