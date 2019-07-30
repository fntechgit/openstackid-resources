<?php namespace App\Http\Controllers;
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
use App\Http\Exceptions\HTTP403ForbiddenException;
use models\summit\Summit;
use models\utils\IEntity;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use ModelSerializers\SerializerRegistry;
use Exception;
/**
 * Trait AddSummitChildElement
 * @package App\Http\Controllers
 */
trait AddSummitChildElement
{
    use BaseSummitAPI;

    /**
     * @param Summit $summit
     * @param array $payload
     * @return IEntity
     */
    abstract protected function addChild(Summit $summit, array $payload):IEntity;

    /**
     * @param array $payload
     * @return array
     */
    abstract function getAddValidationRules(array $payload): array;

    /**
     * @param $summit_id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function add($summit_id){
        try {
            if(!Request::isJson()) return $this->error400();
            $data = Input::json();
            $payload = $data->all();
            $summit = SummitFinderStrategyFactory::build($this->getSummitRepository(), $this->getResourceServerContext())->find($summit_id);
            if (is_null($summit)) return $this->error404();

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $this->getAddValidationRules($payload));

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $child = $this->addChild($summit, $payload);

            return $this->created(SerializerRegistry::getInstance()->getSerializer($child)->serialize());
        }
        catch (ValidationException $ex) {
            Log::warning($ex);
            return $this->error412(array($ex->getMessage()));
        }
        catch(EntityNotFoundException $ex)
        {
            Log::warning($ex);
            return $this->error404(array('message'=> $ex->getMessage()));
        }
        catch (\HTTP401UnauthorizedException $ex) {
            Log::warning($ex);
            return $this->error401();
        }
        catch (HTTP403ForbiddenException $ex) {
            Log::warning($ex);
            return $this->error403();
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

}