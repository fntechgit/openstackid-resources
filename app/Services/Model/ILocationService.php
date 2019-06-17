<?php namespace App\Services\Model;
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
use App\Models\Foundation\Summit\Locations\Banners\SummitLocationBanner;
use models\main\Member;
use models\summit\SummitBookableVenueRoom;
use models\summit\SummitLocationImage;
use models\summit\SummitRoomReservation;
use models\summit\SummitVenueRoom;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\summit\Summit;
use models\summit\SummitAbstractLocation;
use models\summit\SummitVenueFloor;
use Illuminate\Http\UploadedFile;
/**
 * Interface ILocationService
 * @package App\Services\Model
 */
interface ILocationService
{
    /**
     * @param Summit $summit
     * @param array $data
     * @return SummitAbstractLocation
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addLocation(Summit $summit, array $data);

    /**
     * @param Summit $summit
     * @param int $location_id
     * @param array $data
     * @return SummitAbstractLocation
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateLocation(Summit $summit, $location_id, array $data);

    /**
     * @param Summit $summit
     * @param int $location_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteLocation(Summit $summit, $location_id);

    /**
     * @param Summit $summit
     * @param int $venue_id
     * @param array $data
     * @return SummitVenueFloor
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addVenueFloor(Summit $summit, $venue_id, array $data);

    /**
     * @param Summit $summit
     * @param int $venue_id
     * @param int $floor_id
     * @param array $data
     * @return SummitVenueFloor
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateVenueFloor(Summit $summit, $venue_id, $floor_id, array $data);

    /**
     * @param Summit $summit
     * @param int $venue_id
     * @param int $floor_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteVenueFloor(Summit $summit, $venue_id, $floor_id);

    /**
     * @param Summit $summit
     * @param int $venue_id
     * @param int $room_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteVenueRoom(Summit $summit, $venue_id, $room_id);

    /**
     * @param Summit $summit
     * @param $venue_id
     * @param array $data
     * @return SummitVenueRoom
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addVenueRoom(Summit $summit, $venue_id, array $data);

    /**
     * @param Summit $summit
     * @param int $venue_id
     * @param int $room_id
     * @param array $data
     * @return SummitVenueRoom
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateVenueRoom(Summit $summit, $venue_id, $room_id, array $data);

    /**
     * @param Summit $summit
     * @param int $location_id
     * @param array $data
     * @return SummitLocationBanner
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addLocationBanner(Summit $summit, $location_id, array $data);

    /**
     * @param Summit $summit
     * @param int $location_id
     * @param int $banner_id
     * @param array $data
     * @return SummitLocationBanner
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateLocationBanner(Summit $summit, $location_id, $banner_id, array $data);

    /**
     * @param Summit $summit
     * @param int $location_id
     * @param int $banner_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteLocationBanner(Summit $summit, $location_id, $banner_id);

    /**
     * @param Summit $summit
     * @param int $location_id
     * @param array $metadata
     * @param $file
     * @return SummitLocationImage
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addLocationMap(Summit $summit, $location_id, array $metadata, UploadedFile $file);

    /**
     * @param Summit $summit
     * @param int $location_id
     * @param int $map_id
     * @param array $metadata
     * @param UploadedFile|null $file
     * @return SummitLocationImage
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateLocationMap(Summit $summit, $location_id, $map_id, array $metadata, UploadedFile $file = null);

    /**
     * @param Summit $summit
     * @param int $location_id
     * @param int $map_id
     * @return SummitAbstractLocation
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteLocationMap(Summit $summit, $location_id, $map_id);

    /**
     * @param Summit $summit
     * @param int $location_id
     * @param array $metadata
     * @param $file
     * @return SummitLocationImage
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addLocationImage(Summit $summit, $location_id, array $metadata, UploadedFile $file);

    /**
     * @param Summit $summit
     * @param int $location_id
     * @param int $image_id
     * @param array $metadata
     * @param UploadedFile|null $file
     * @return SummitLocationImage
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateLocationImage(Summit $summit, $location_id, $image_id, array $metadata, UploadedFile $file = null);

    /**
     * @param Summit $summit
     * @param int $location_id
     * @param int $image_id
     * @return SummitAbstractLocation
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteLocationImage(Summit $summit, $location_id, $image_id);

    /**
     * @param Summit $summit
     * @param int $room_id
     * @param array $payload
     * @return SummitRoomReservation
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addBookableRoomReservation(Summit $summit, int $room_id, array $payload):SummitRoomReservation;

    /**
     * @param array $data
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function processBookableRoomPayment(array $payload):void;

    /**
     * @param Summit $sumit
     * @param Member $owner
     * @param int $reservation_id
     * @return SummitRoomReservation
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function cancelReservation(Summit $sumit, Member $owner, int $reservation_id):SummitRoomReservation;

    /**
     * @param SummitBookableVenueRoom $room
     * @param int $reservation_id
     * @param int $amount
     * @return SummitRoomReservation
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function refundReservation(SummitBookableVenueRoom $room, int $reservation_id, int $amount):SummitRoomReservation;


        /**
     * @param Summit $summit
     * @param int $venue_id
     * @param int $room_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteVenueBookableRoom(Summit $summit, $venue_id, $room_id);

    /**
     * @param Summit $summit
     * @param $venue_id
     * @param array $data
     * @return SummitVenueRoom
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addVenueBookableRoom(Summit $summit, $venue_id, array $data);

    /**
     * @param Summit $summit
     * @param int $venue_id
     * @param int $room_id
     * @param array $data
     * @return SummitVenueRoom
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateVenueBookableRoom(Summit $summit, $venue_id, $room_id, array $data);

    /**
     * @param Summit $summit
     * @param int $venue_id
     * @param int $room_id
     * @param int $attribute_id
     * @return SummitBookableVenueRoom
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addVenueBookableRoomAttribute(Summit $summit, int $venue_id, int $room_id, int $attribute_id):SummitBookableVenueRoom;

    /**
     * @param Summit $summit
     * @param int $venue_id
     * @param int $room_id
     * @param int $attribute_id
     * @return SummitBookableVenueRoom
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteVenueBookableRoomAttribute(Summit $summit, int $venue_id, int $room_id, int $attribute_id):SummitBookableVenueRoom;
}