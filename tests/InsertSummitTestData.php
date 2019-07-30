<?php
/**
 * Copyright 2020 OpenStack Foundation
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
use LaravelDoctrine\ORM\Facades\EntityManager;
use LaravelDoctrine\ORM\Facades\Registry;
use models\utils\SilverstripeBaseModel;
use models\summit\SummitVenue;
use models\summit\Summit;
/**
 * Trait InsertSummitTestData
 * @package Tests
 */
trait InsertSummitTestData
{
    /**
     * @var Summit
     */
    protected $summit;

    /**
     * @var SummitVenue
     */
    protected $mainVenue;

    protected function insertTestData(){
        $summit_repo = EntityManager::getRepository(Summit::class);
        $this->summit = new Summit();
        $this->summit->setActive(true);
        // set feed type (sched)
        $this->summit->setApiFeedUrl("");
        $this->summit->setApiFeedKey("");
        $this->summit->setTimeZoneId("America/Chicago");
        $this->summit->setBeginDate(new \DateTime("2019-09-1"));
        $this->summit->setEndDate(new \DateTime("2019-09-30"));


        $this->mainVenue = new SummitVenue();
        $this->mainVenue->setIsMain(true);
        $this->summit->addLocation($this->mainVenue);


        $em = Registry::getManager(SilverstripeBaseModel::EntityManager);
        $em->persist($this->summit);
        $em->flush();
    }
}