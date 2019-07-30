<?php namespace models\summit;
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
use App\Models\Foundation\Summit\PromoCodes\PromoCodesConstants;
use Doctrine\ORM\Mapping AS ORM;
use models\main\Member;
/**
 * @ORM\Entity
 * @ORM\Table(name="MemberSummitRegistrationPromoCode")
 * Class MemberSummitRegistrationPromoCode
 * @package models\summit
 */
class MemberSummitRegistrationPromoCode
    extends SummitRegistrationPromoCode
    implements IOwnablePromoCode
{
    use MemberPromoCodeTrait;

    const ClassName = 'MEMBER_PROMO_CODE';

    public static $metadata = [
        'class_name' => self::ClassName,
        'first_name' => 'string',
        'last_name'  => 'string',
        'email'      => 'string',
        'type'       => PromoCodesConstants::MemberSummitRegistrationPromoCodeTypes,
        'owner_id'   => 'integer'
    ];

    /**
     * @return array
     */
    public static function getMetadata(){
        return array_merge(SummitRegistrationPromoCode::getMetadata(), self::$metadata);
    }

    /**
     * @return string
     */
    public function getClassName(){
        return self::ClassName;
    }

    public function getOwnerFullname(): string
    {
        return  $this->owner->getFullName();
    }

    public function getOwnerEmail(): string
    {
        return $this->owner->getEmail();
    }

}