<?php namespace models\summit;
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

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="SummitAirport")
 * Class SummitAirport
 * @package models\summit
 */
class SummitAirport extends SummitExternalLocation
{
    /**
     * @return string
     */
    public function getClassName(){
        return 'SummitAirport';
    }

    /**
     * @return string
     */
    public function getAirportType()
    {
        return $this->airport_type;
    }

    /**
     * @param string $airport_type
     */
    public function setAirportType($airport_type)
    {
        $this->airport_type = $airport_type;
    }

    /**
     * @ORM\Column(name="Type", type="string")
     */
    private $airport_type;
}