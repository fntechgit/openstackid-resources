<?php namespace App\Models\Foundation\Summit\Repositories;
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
use App\Models\Foundation\Summit\Defaults\DefaultSummitEventType;
use models\utils\IBaseRepository;
/**
 * Interface IDefaultSummitEventTypeRepository
 * @package App\Models\Foundation\Summit\Repositories
 */
interface IDefaultSummitEventTypeRepository
    extends IBaseRepository
{
    /**
     * @param string $type
     * @return null|DefaultSummitEventType
     */
    public function getByType($type);
}