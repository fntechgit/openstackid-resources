<?php namespace App\Services\Model;
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
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\Member;
use models\summit\Summit;
use models\summit\SummitAttendeeBadge;
use models\summit\SummitAttendeeTicket;
use models\summit\SummitOrder;
use Illuminate\Http\UploadedFile;
/**
 * Interface ISummitOrder
 * @package App\Services\Model
 */
interface ISummitOrderService
{
    /**
     * @param Member|null $owner
     * @param Summit $summit
     * @param array $payload
     * @return SummitOrder
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function reserve(?Member $owner, Summit $summit, array $payload):SummitOrder;

    /**
     * @param Summit $summit
     * @param string $order_hash
     * @param array $payload
     * @return SummitOrder
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function checkout(Summit $summit, string $order_hash, array $payload):SummitOrder;

    /**
     * @param Summit $summit
     * @param string $order_hash
     * @return SummitAttendeeTicket|null
     */
    public function getMyTicketByOrderHash(Summit $summit, string $order_hash):?SummitAttendeeTicket;

    /**
     * @param Summit $summit
     * @param string $order_hash
     * @return SummitOrder
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function cancel(Summit $summit, string $order_hash):SummitOrder;

    /**
     * @param Member $current_user
     * @param int $order_id
     * @param array $payload
     * @return SummitOrder
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateMyOrder(Member $current_user, int $order_id, array $payload):SummitOrder;

    /**
     * @param Member $current_user
     * @param int $order_id
     * @param int $ticket_id
     * @return SummitAttendeeTicket
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function revokeTicket(Member $current_user, int $order_id, int $ticket_id):SummitAttendeeTicket;

    /**
     * @param Member $current_user
     * @param int $order_id
     * @param int $ticket_id
     * @param array $payload
     * @return SummitAttendeeTicket
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function ownerAssignTicket(Member $current_user, int $order_id, int $ticket_id, array $payload):SummitAttendeeTicket;

    /**
     * @param Summit $summit
     * @param int $order_id
     * @param array $payload
     * @return SummitAttendeeTicket
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addTicket(Summit $summit, int $order_id, array $payload):SummitAttendeeTicket;

    /**
     * @param Summit $summit
     * @param int $order_id
     * @param int $ticket_id
     * @param array $payload
     * @return SummitAttendeeTicket
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateTicket(Summit $summit, int $order_id, int $ticket_id, array $payload):SummitAttendeeTicket;

    /**
     * @param Member $current_user
     * @param int $order_id
     * @return SummitOrder
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function requestRefundOrder(Member $current_user, int $order_id):SummitOrder;

    /**
     * @param Member $current_user
     * @param int $order_id
     * @param int $ticket_id
     * @return SummitAttendeeTicket
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function requestRefundTicket(Member $current_user, int $order_id, int $ticket_id): SummitAttendeeTicket;

    /**
     * @param array $payload
     * @throws \Exception
     */
    public function processPayment(array $payload): void;

    /**
     * @param int $minutes
     * @param int $max
     */
    public function revokeReservedOrdersOlderThanNMinutes(int $minutes, int $max = 100):void;

    /**
     * @param $ticket_id
     * @param string $format
     * @param Member|null $current_user
     * @param int|null $order_id
     * @param Summit|null $summit
     * @return string
     */
    public function renderTicketByFormat($ticket_id, string $format = "pdf", ?Member $current_user = null, ?int $order_id = null, ?Summit $summit = null):string;

    /**
     * @param string $hash
     */
    public function regenerateTicketHash(string $hash):void;

    /**
     * @param string $hash
     * @return SummitAttendeeTicket
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function getTicketByHash(string $hash):SummitAttendeeTicket;

    /**
     * @param string $hash
     * @return SummitAttendeeTicket
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateTicketByHash(string $hash, array $payload):SummitAttendeeTicket;

    /**
     * @param  Member $current_user
     * @param  int $ticket_id
     * @return SummitAttendeeTicket
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateTicketById(Member $current_user,int $ticket_id, array $payload):SummitAttendeeTicket;

    /**
     * @param Summit $summit
     * @param array $payload
     * @return SummitOrder
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function createOrderSingleTicket(Summit $summit, array $payload):SummitOrder;

    /**
     * @param Summit $summit
     * @param int $order_id
     * @param array $payload
     * @return SummitOrder
     */
    public function updateOrder(Summit $summit, int $order_id, array $payload):SummitOrder;

    /**
     * @param Summit $summit
     * @param int $order_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteOrder(Summit $summit, int $order_id);

    /**
     * @param Summit $summit
     * @param int $order_id
     * @param float $amount
     * @return SummitOrder
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function refundOrder(Summit $summit, int $order_id, float $amount):SummitOrder;

    /**
     * @param Summit $summit
     * @param int|string $ticket_id
     * @param float $amount
     * @return SummitAttendeeTicket
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function refundTicket(Summit $summit, $ticket_id, float $amount):SummitAttendeeTicket;


    /**
     * @param Summit $summit
     * @param int|string $ticket_id
     * @param int $type_id
     * @return SummitAttendeeBadge
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateBadgeType(Summit $summit, $ticket_id, int $type_id):SummitAttendeeBadge;

    /**
     * @param Summit $summit
     * @param int|string $ticket_id
     * @param int $feature_id
     * @return SummitAttendeeBadge
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addAttendeeBadgeFeature(Summit $summit, $ticket_id, int $feature_id):SummitAttendeeBadge;

    /**
     * @param Summit $summit
     * @param int|string $ticket_id
     * @param int $feature_id
     * @return SummitAttendeeBadge
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function removeAttendeeBadgeFeature(Summit $summit, $ticket_id, int $feature_id):SummitAttendeeBadge;

    /**
     * @param Summit $summit
     * @param int|string $ticket_id
     * @param Member $requestor
     * @return SummitAttendeeBadge
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function printAttendeeBadge(Summit $summit, $ticket_id, Member $requestor):SummitAttendeeBadge;

    /**
     * @param Summit $summit
     * @param int|string $ticket_id
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteBadge(Summit $summit, $ticket_id):void;

    /**
     * @param Summit $summit
     * @param int|string $ticket_id
     * @param array $payload
     * @return SummitAttendeeBadge
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function createBadge(Summit $summit, $ticket_id, array $payload):SummitAttendeeBadge;

    /**
     * @param int $order_id
     * @param int $ticket_id
     * @return SummitAttendeeTicket
     * @throws EntityNotFoundException
     */
    public function reInviteAttendee(int $order_id, int $ticket_id):SummitAttendeeTicket;

    /**
     * @param Summit $summit
     * @param $ticket_id
     * @return SummitAttendeeTicket
     * @throws \Exception
     */
    public function getTicket(Summit $summit, $ticket_id):SummitAttendeeTicket;

    public function processAllOrderReminder():void;

    /**
     * @param SummitOrder $order
     * @throws \Exception
     */
    public function processOrderReminder(SummitOrder $order): void;

    /**
     * @param SummitAttendeeTicket $ticket
     * @throws \Exception
     */
    public function processTicketReminder(SummitAttendeeTicket $ticket):void;

    /**
     * @param Summit $summit
     * @throws \Exception
     */
    public function processSummitOrderReminders(Summit $summit):void;

    /**
     * @param Summit $summit
     * @param UploadedFile $csv_file
     */
    public function importTicketData(Summit $summit, UploadedFile $csv_file):void;

    /**
     * @param Summit $summit
     * @param array $payload
     * @return void
     * @throws ValidationException
     */
    public function ingestExternalTicketData(Summit $summit, array $payload):void;

}