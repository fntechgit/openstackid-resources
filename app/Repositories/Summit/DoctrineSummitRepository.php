<?php namespace App\Repositories\Summit;
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
use Doctrine\ORM\QueryBuilder;
use Illuminate\Support\Facades\Log;
use models\summit\ISummitRepository;
use models\summit\Summit;
use App\Repositories\SilverStripeDoctrineRepository;
use utils\DoctrineHavingFilterMapping;
/**
 * Class DoctrineSummitRepository
 * @package App\Repositories\Summit
 */
final class DoctrineSummitRepository
    extends SilverStripeDoctrineRepository
    implements ISummitRepository
{

    /**
     * @return array
     */
    protected function getFilterMappings()
    {
        return [
            'start_date'               => 'e.begin_date:datetime_epoch',
            'end_date'                 => 'e.end_date:datetime_epoch',
            'registration_begin_date'  => 'e.registration_begin_date:datetime_epoch',
            'registration_end_date'    => 'e.registration_end_date:datetime_epoch',
            'ticket_types_count'       => new DoctrineHavingFilterMapping("","tt.summit", "count(tt.id) :operator :value"),
        ];
    }

    /**
     * @return array
     */
    protected function getOrderMappings()
    {
        return [
            'id'                      => 'e.id',
            'name'                    => 'e.name',
            'begin_date'              => 'e.begin_date',
            'registration_begin_date' => 'e.registration_begin_date',
        ];
    }

    /**
     * @param QueryBuilder $query
     * @return QueryBuilder
     */
    protected function applyExtraFilters(QueryBuilder $query){
        return $query;
    }

    /**
     * @param QueryBuilder $query
     * @return QueryBuilder
     */
    protected function applyExtraJoins(QueryBuilder $query){
        $query = $query->leftJoin("e.ticket_types", "tt");
        return $query;
    }

    /**
     * @return Summit
     */
    public function getCurrent()
    {
        $res = $this->getEntityManager()->createQueryBuilder()
            ->select("s")
            ->from($this->getBaseEntity(), "s")
            ->where('s.active = 1')
            ->orderBy('s.begin_date', 'DESC')
            ->getQuery()
            ->getResult();
        if (count($res) == 0) return null;
        return $res[0];
    }

    /**
     * @return Summit[]
     */
    public function getCurrentAndFutureSummits(){
        $now_utc = new \DateTime('now', new \DateTimeZone('UTC'));
        return $this->getEntityManager()->createQueryBuilder()
            ->select("s")
            ->from($this->getBaseEntity(), "s")
            ->where('s.begin_date <= :now and s.end_date >= :now')
            ->orWhere('s.begin_date >= :now')
            ->orderBy('s.begin_date', 'DESC')
            ->setParameter('now', $now_utc)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Summit[]
     */
    public function getAvailables()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select("s")
            ->from($this->getBaseEntity(), "s")
            ->where('s.available_on_api = 1')
            ->orderBy('s.begin_date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Summit[]
     */
    public function getAllOrderedByBeginDate()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select("s")
            ->from($this->getBaseEntity(), "s")
            ->orderBy('s.begin_date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return string
     */
    protected function getBaseEntity()
    {
        return Summit::class;
    }

    /**
     * @param string $name
     * @return Summit
     */
    public function getByName($name)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select("s")
            ->from($this->getBaseEntity(), "s")
            ->where('s.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $slug
     * @return Summit|null
     */
    public function getBySlug(string $slug):?Summit
    {
        try {
            return $this->getEntityManager()->createQueryBuilder()
                ->select("s")
                ->from($this->getBaseEntity(), "s")
                ->where('s.slug = :slug')
                ->setParameter('slug', strtolower($slug))
                ->getQuery()
                ->getOneOrNullResult();
        }
        catch (\Exception $ex){
            Log::warning($ex);
            return null;
        }
    }

    /**
     * @return Summit
     */
    public function getActive()
    {
        $res = $this->getEntityManager()->createQueryBuilder()
            ->select("s")
            ->from($this->getBaseEntity(), "s")
            ->where('s.active = 1')
            ->orderBy('s.begin_date', 'DESC')
            ->getQuery()
            ->getResult();
        if (count($res) == 0) return null;
        return $res[0];
    }

    /**
     * @return Summit
     */
    public function getCurrentAndAvailable()
    {
        $res = $this->getEntityManager()->createQueryBuilder()
            ->select( "s")
            ->from($this->getBaseEntity(), 's')
            ->where('s.active = 1')
            ->andWhere('s.available_on_api = 1')
            ->orderBy('s.begin_date', 'DESC')
            ->getQuery()
            ->getResult();
        if (count($res) == 0) return null;
        return $res[0];
    }

    /**
     * @return Summit[]
     */
    public function getWithExternalFeed(): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select("e")
            ->from($this->getBaseEntity(), "e")
            ->where("e.api_feed_type is not null")
            ->andWhere("e.api_feed_type <> ''")
            ->andWhere("e.api_feed_url is not null")
            ->andWhere("e.api_feed_url <> ''")
            ->andWhere("e.api_feed_key is not null")
            ->andWhere("e.api_feed_key <>''")
            ->andWhere("e.end_date >= :now")
            ->orderBy('e.id', 'DESC')
            ->setParameter("now", new \DateTime('now', new \DateTimeZone('UTC')))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Summit[]
     */
    public function getAllWithExternalRegistrationFeed():array {
        return $this->getEntityManager()->createQueryBuilder()
            ->select("e")
            ->from($this->getBaseEntity(), "e")
            ->where("e.api_feed_type is not null")
            ->andWhere("e.external_registration_feed_type <> ''")
            ->andWhere("e.external_registration_feed_type is not null")
            ->andWhere("e.external_registration_feed_api_key <> ''")
            ->andWhere("e.external_registration_feed_api_key is not null")
            ->andWhere("e.external_summit_id <> ''")
            ->andWhere("e.external_summit_id is not null")
            ->andWhere("e.end_date >= :now")
            ->orderBy('e.id', 'DESC')
            ->setParameter("now", new \DateTime('now', new \DateTimeZone('UTC')))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function getNotEnded(): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select("e")
            ->from($this->getBaseEntity(), "e")
            ->where("e.end_date >= :now")
            ->orderBy('e.id', 'DESC')
            ->setParameter("now", new \DateTime('now', new \DateTimeZone('UTC')))
            ->getQuery()
            ->getResult();
    }
}