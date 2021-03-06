<?php namespace App\Http\Controllers;
/**
 * Copyright 2016 OpenStack Foundation
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
use Exception;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\oauth2\IResourceServerContext;
use models\summit\ConfirmationExternalOrderRequest;
use models\summit\IEventFeedbackRepository;
use models\summit\ISpeakerRepository;
use models\summit\ISummitEventRepository;
use models\summit\ISummitRepository;
use ModelSerializers\ISerializerTypeSelector;
use ModelSerializers\SerializerRegistry;
use services\model\ISummitService;
use utils\Filter;
use utils\FilterElement;
use utils\Order;
use utils\OrderElement;
use Illuminate\Http\Request as LaravelRequest;
/**
 * Class OAuth2SummitApiController
 * @package App\Http\Controllers
 */
final class OAuth2SummitApiController extends OAuth2ProtectedController
{

    /**
     * @var ISummitService
     */
    private $summit_service;

    /**
     * @var ISpeakerRepository
     */
    private $speaker_repository;

    /**
     * @var ISummitEventRepository
     */
    private $event_repository;

    /**
     * @var IEventFeedbackRepository
     */
    private $event_feedback_repository;

    /**
     * @var ISerializerTypeSelector
     */
    private $serializer_type_selector;

    /**
     * OAuth2SummitApiController constructor.
     * @param ISummitRepository $summit_repository
     * @param ISummitEventRepository $event_repository
     * @param ISpeakerRepository $speaker_repository
     * @param IEventFeedbackRepository $event_feedback_repository
     * @param ISummitService $summit_service
     * @param ISerializerTypeSelector $serializer_type_selector
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        ISummitRepository $summit_repository,
        ISummitEventRepository $event_repository,
        ISpeakerRepository $speaker_repository,
        IEventFeedbackRepository $event_feedback_repository,
        ISummitService $summit_service,
        ISerializerTypeSelector $serializer_type_selector,
        IResourceServerContext $resource_server_context
    ) {
        parent::__construct($resource_server_context);

        $this->repository                = $summit_repository;
        $this->speaker_repository        = $speaker_repository;
        $this->event_repository          = $event_repository;
        $this->event_feedback_repository = $event_feedback_repository;
        $this->serializer_type_selector  = $serializer_type_selector;
        $this->summit_service            = $summit_service;
    }

    use ParametrizedGetAll;

    /**
     * @return mixed
     */
    public function getSummits()
    {

        return $this->getAll(
            function(){
                return [
                    'start_date'              => ['==','<','>','<=','>='],
                    'end_date'                => ['==','<','>','<=','>='],
                    'registration_begin_date' => ['==','<','>','<=','>='],
                    'registration_end_date'   => ['==','<','>','<=','>='],
                    'ticket_types_count'      => ['==','<','>','<=','>=','<>'],
                ];
            },
            function(){
                return [
                    'start_date'              => 'sometimes|required|date_format:U',
                    'end_date'                => 'sometimes|required_with:start_date|date_format:U|after:start_date',
                    'registration_begin_date' => 'sometimes|required|date_format:U',
                    'registration_end_date'   => 'sometimes|required_with:start_date|date_format:U|after:registration_begin_date',
                    'ticket_types_count'      => 'sometimes|required|integer'
                ];
            },
            function(){
                return [
                    'id',
                    'name',
                    'begin_date',
                    'registration_begin_date'
                ];
            },
            function($filter){
                if($filter instanceof Filter){
                    $filter->addFilterCondition(FilterElement::makeEqual('available_on_api', '1'));
                }
                return $filter;
            },
            function(){
                return $this->serializer_type_selector->getSerializerType();
            },
            function(){
                return new Order([
                    OrderElement::buildAscFor("begin_date"),
                ]);
            },
            function (){
                return PHP_INT_MAX;
            }
        );
    }

    /**
     * @return mixed
     */
    public function getAllSummits(){
        return $this->getAll(
            function(){
               return [
                   'start_date'              => ['==','<','>','=>','>='],
                   'end_date'                => ['==','<','>','=>','>='],
                   'registration_begin_date' => ['==','<','>','=>','>='],
                   'registration_end_date'   => ['==','<','>','=>','>='],
                   'ticket_types_count'      => ['==','<','>','=>','>=','<>'],
               ];
            },
            function(){
                return [
                    'start_date'              => 'sometimes|required|date_format:U',
                    'end_date'                => 'sometimes|required_with:start_date|date_format:U|after:start_date',
                    'registration_begin_date' => 'sometimes|required|date_format:U',
                    'registration_end_date'   => 'sometimes|required_with:start_date|date_format:U|after:registration_begin_date',
                    'ticket_types_count'      => 'sometimes|required|integer'
                ];
            },
            function(){
                return [
                    'id',
                    'name',
                    'start_date',
                    'registration_begin_date'
                ];
            },
            function($filter){
                return $filter;
            },
            function(){
                return $this->serializer_type_selector->getSerializerType();
            }
            ,
            function(){
                return new Order([
                    OrderElement::buildAscFor("begin_date"),
                ]);
            },
            function (){
                return PHP_INT_MAX;
            }
        );
    }
    /**
     * @param $summit_id
     * @return mixed
     */
    public function getSummit($summit_id)
    {
        $expand = Request::input('expand', '');
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $serializer_type = $this->serializer_type_selector->getSerializerType();
            return $this->ok(SerializerRegistry::getInstance()->getSerializer($summit, $serializer_type)->serialize($expand));
        }
        catch(HTTP403ForbiddenException $ex1){
            Log::warning($ex1);
            return $this->error403();
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }


    /**
     * @return mixed
     */
    public function getAllCurrentSummit()
    {
        $expand = Request::input('expand', '');

        try {
            $summit = $this->repository->getCurrent();
            if (is_null($summit)) return $this->error404();
            $serializer_type = $this->serializer_type_selector->getSerializerType();
            return $this->ok(SerializerRegistry::getInstance()->getSerializer($summit, $serializer_type)->serialize($expand));
        }
        catch(HTTP403ForbiddenException $ex1){
            Log::warning($ex1);
            return $this->error403();
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    public function getAllSummitByIdOrSlug($id){

        $expand = Request::input('expand', '');

        try {
            $summit = $this->repository->getById(intval($id));
            if(is_null($summit))
                $summit = $this->repository->getBySlug(trim($id));
            if (is_null($summit)) return $this->error404();
            $serializer_type = $this->serializer_type_selector->getSerializerType();
            return $this->ok(SerializerRegistry::getInstance()->getSerializer($summit, $serializer_type)->serialize($expand));
        }
        catch(HTTP403ForbiddenException $ex1){
            Log::warning($ex1);
            return $this->error403();
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    public function addSummit(){
        try {

            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();

            $rules = SummitValidationRulesFactory::build($payload);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules, $messages = [
                'slug.required' => 'A Slug is required.',
                'schedule_start_date.before_or_equal' => 'Show on schedule page needs to be after the start of the Show And Before of the Show End.',
            ]);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $summit = $this->summit_service->addSummit($payload);
            $serializer_type = $this->serializer_type_selector->getSerializerType();
            return $this->created(SerializerRegistry::getInstance()->getSerializer($summit, $serializer_type)->serialize());
        }
        catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412([$ex1->getMessage()]);
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(['message'=> $ex2->getMessage()]);
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function updateSummit($summit_id){
        try {

            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();

            $rules = SummitValidationRulesFactory::build($payload, true);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $summit = $this->summit_service->updateSummit($summit_id, $payload);
            $serializer_type = $this->serializer_type_selector->getSerializerType();
            return $this->updated(SerializerRegistry::getInstance()->getSerializer($summit, $serializer_type)->serialize());
        }
        catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412([$ex1->getMessage()]);
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(['message'=> $ex2->getMessage()]);
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function deleteSummit($summit_id){
        try {

            $this->summit_service->deleteSummit($summit_id);

            return $this->deleted();
        }
        catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412([$ex1->getMessage()]);
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(['message'=> $ex2->getMessage()]);
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getSummitEntityEvents($summit_id)
    {
        try {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $current_member    = $this->resource_server_context->getCurrentUser();
            $current_member_id = is_null($current_member)? null : $current_member->getId();

            $last_event_id = Request::input('last_event_id', null);
            $from_date     = Request::input('from_date', null);
            $limit         = Request::input('limit', 25);

            $rules = [
                'last_event_id' => 'sometimes|required|integer',
                'from_date'     => 'sometimes|required|integer',
                'limit'         => 'sometimes|required|integer',
            ];

            $data = [];

            if (!is_null($last_event_id))
            {
                $data['last_event_id'] = $last_event_id;
            }

            if (!is_null($from_date))
            {
                $data['from_date'] = $from_date;
            }

            if(!is_null($limit)){
                $data['limit'] = $limit;
            }

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($data, $rules);

            if ($validation->fails())
            {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            if (!is_null($from_date))
            {
                $from_date = new \DateTime("@$from_date", new \DateTimeZone("UTC"));
            }

            list($last_event_id, $last_event_date, $list) = $this->summit_service->getSummitEntityEvents
            (
                $summit,
                $current_member_id,
                $from_date,
                intval($last_event_id),
                intval($limit)
            );

            return $this->ok
            (
            //todo: send this new response once that testing is done!
            /*array
            (
                'events'          => $list,
                'last_event_id'   => $last_event_id,
                'last_event_date' => $last_event_date->getTimestamp()
            )*/
                $list
            );
        }
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $external_order_id
     * @return mixed
     */
    public function getExternalOrder($summit_id, $external_order_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $order = $this->summit_service->getExternalOrder($summit, $external_order_id);
            return $this->ok($order);
        }
        catch (EntityNotFoundException $ex1) {
            Log::warning($ex1);
            return $this->error404(array('message' => $ex1->getMessage()));
        }
        catch (ValidationException $ex2) {
            Log::warning($ex2);
            return $this->error412($ex2->getMessages());
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $external_order_id
     * @param $external_attendee_id
     * @return mixed
     */
    public function confirmExternalOrderAttendee($summit_id, $external_order_id, $external_attendee_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $current_member = $this->resource_server_context->getCurrentUser();
            if (is_null($current_member)) throw new \HTTP401UnauthorizedException;

            $attendee = $this->summit_service->confirmExternalOrderAttendee
            (
                new ConfirmationExternalOrderRequest
                (
                    $summit,
                    $current_member->getId(),
                    trim($external_order_id),
                    trim($external_attendee_id)
                )
            );

            return $this->ok(SerializerRegistry::getInstance()->getSerializer($attendee)->serialize());
        }
        catch (EntityNotFoundException $ex1) {
            Log::warning($ex1);
            return $this->error404(array('message' => $ex1->getMessage()));
        }
        catch (ValidationException $ex2) {
            Log::warning($ex2);
            return $this->error412($ex2->getMessages());
        }
        catch (\HTTP401UnauthorizedException $ex3) {
            Log::warning($ex3);
            return $this->error401();
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @return ISummitRepository
     */
    protected function getSummitRepository(): ISummitRepository
    {
        return $this->repository;
    }

    /**
     * @param LaravelRequest $request
     * @param $summit_id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function addSummitLogo(LaravelRequest $request, $summit_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $file = $request->file('file');
            if (is_null($file)) {
                return $this->error412(array('file param not set!'));
            }

            $photo = $this->summit_service->addSummitLogo($summit_id, $file);

            return $this->created(SerializerRegistry::getInstance()->getSerializer($photo)->serialize());

        } catch (EntityNotFoundException $ex1) {
            Log::warning($ex1);
            return $this->error404();
        } catch (ValidationException $ex2) {
            Log::warning($ex2);
            return $this->error412(array($ex2->getMessage()));
        } catch (\HTTP401UnauthorizedException $ex3) {
            Log::warning($ex3);
            return $this->error401();
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function deleteSummitLogo($summit_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $this->summit_service->deleteSummitLogo($summit_id);

            return $this->deleted();

        } catch (EntityNotFoundException $ex1) {
            Log::warning($ex1);
            return $this->error404();
        } catch (ValidationException $ex2) {
            Log::warning($ex2);
            return $this->error412(array($ex2->getMessage()));
        } catch (\HTTP401UnauthorizedException $ex3) {
            Log::warning($ex3);
            return $this->error401();
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

}