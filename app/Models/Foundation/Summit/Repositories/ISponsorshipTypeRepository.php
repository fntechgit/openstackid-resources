<?php namespace App\Models\Foundation\Summit\Repositories;
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
use models\summit\SponsorshipType;
use models\utils\IBaseRepository;
/**
 * Interface ISponsorshipTypeRepository
 * @package App\Models\Foundation\Summit\Repositories
 */
interface ISponsorshipTypeRepository extends IBaseRepository
{
    /**
     * @param string $name
     * @return SponsorshipType|null
     */
    public function getByName(string $name):?SponsorshipType;

    /**
     * @param string $label
     * @return SponsorshipType|null
     */
    public function getByLabel(string $label):?SponsorshipType;

    /**
     * @return int
     */
    public function getMaxOrder():int;
}