<?php namespace models\summit\CalendarSync\WorkQueue;
/**
 * Copyright 2017 OpenStack Foundation
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
use models\summit\SummitAbstractLocation;
/**
 * Class AdminSummitLocationActionSyncWorkRequest
 * @ORM\Entity
 * @ORM\Table(name="AdminSummitLocationActionSyncWorkRequest")
 * @package models\summit\CalendarSync\WorkQueue
 */
final class AdminSummitLocationActionSyncWorkRequest
extends AdminScheduleSummitActionSyncWorkRequest
{
    const SubType = 'ADMIN_LOCATION';
    /**
     * @ORM\ManyToOne(targetEntity="models\summit\SummitAbstractLocation", cascade={"persist"})
     * @ORM\JoinColumn(name="LocationID", referencedColumnName="ID")
     * @var SummitAbstractLocation
     */
    private $location;

    /**
     * @return SummitAbstractLocation
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return int
     */
    public function getLocationId()
    {
        try{
            return is_null($this->location) ? 0 : $this->location->getId();
        }
        catch (\Exception $ex){
            return 0;
        }
    }

    /**
     * @param SummitAbstractLocation $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }
}