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

use Doctrine\ORM\Tools\Pagination\Paginator;
use models\summit\ISummitEventRepository;
use models\summit\SummitEvent;
use App\Repositories\SilverStripeDoctrineRepository;
use utils\DoctrineJoinFilterMapping;
use utils\Filter;
use utils\Order;
use utils\PagingInfo;
use utils\PagingResponse;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class DoctrineSummitEventRepository
 * @package App\Repositories\Summit
 */
final class DoctrineSummitEventRepository
    extends SilverStripeDoctrineRepository
    implements ISummitEventRepository
{

    private static $forbidded_classes = [
        'models\\summit\\SummitGroupEvent'
    ];

    /**
     * @param SummitEvent $event
     * @return SummitEvent[]
     */
    public function getPublishedOnSameTimeFrame(SummitEvent $event)
    {
        $summit     = $event->getSummit();
        $end_date   = $event->getEndDate();
        $start_date = $event->getStartDate();

        return $this->getEntityManager()->createQueryBuilder()
            ->select("e")
            ->from(\models\summit\SummitEvent::class, "e")
            ->join('e.summit', 's', Join::WITH, " s.id = :summit_id")
            ->where('e.published = 1')
            ->andWhere('e.start_date < :end_date')
            ->andWhere("not e INSTANCE OF ('" . implode("','", self::$forbidded_classes) . "')")
            ->andWhere('e.end_date > :start_date')
            ->setParameter('summit_id', $summit->getId())
            ->setParameter('start_date', $start_date)
            ->setParameter('end_date', $end_date)->getQuery()->getResult();
    }

    /**
     * @return array
     */
    protected function getFilterMappings()
    {
        return [
            'title'         => 'e.title:json_string',
            'published'     => 'e.published',
            'start_date'    => 'e.start_date:datetime_epoch',
            'end_date'      => 'e.end_date:datetime_epoch',
            'tags'          => new DoctrineJoinFilterMapping
            (
                'e.tags',
                't',
                "t.tag :operator ':value'"
            ),
            'location_id'=> new DoctrineJoinFilterMapping
            (
                'e.location',
                'l',
                "l.id :operator :value"
            ),
            'summit_id'=> new DoctrineJoinFilterMapping
            (
                'e.summit',
                's',
                "s.id  :operator :value"
            ),
            'event_type_id' => new DoctrineJoinFilterMapping
            (
                'e.type',
                'et',
                "et.id :operator :value"
            ),
            'track_id' => new DoctrineJoinFilterMapping
            (
                'e.category',
                'c',
                "c.id :operator :value"
            ),
            'speaker' => new DoctrineJoinFilterMapping
            (
                'e.speakers',
                'sp',
                "concat(sp.first_name, ' ', sp.last_name) :operator ':value'"
            ),
        ];
    }

    /**
     * @return array
     */
    protected function getOrderMappings()
    {
        return [
            'title'       => 'e.title',
            'id'          => 'e.id',
            'start_date'  => 'e.start_date',
            'end_date'    => 'e.end_date',
            'created'     => 'e.created',
        ];
    }

    /**
     * @param PagingInfo $paging_info
     * @param Filter|null $filter
     * @param Order|null $order
     * @return PagingResponse
     */
    public function getAllByPage(PagingInfo $paging_info, Filter $filter = null, Order $order = null)
    {
        $class  = count($filter->getFilter('speaker')) > 0?
            \models\summit\Presentation::class :
            \models\summit\SummitEvent::class;

        $query  = $this->getEntityManager()->createQueryBuilder()
            ->select("e")
            ->from($class, "e");

        if(!is_null($filter)){

            $filter->apply2Query($query, $this->getFilterMappings());
        }

        if (!is_null($order)) {
            $order->apply2Query($query, $this->getOrderMappings());
        } else {
            //default order
            $query = $query->addOrderBy("e.start_date",'ASC');
            $query = $query->addOrderBy("e.end_date", 'ASC');
        }

        $query= $query
            ->andWhere("not e INSTANCE OF ('" . implode("','", self::$forbidded_classes) . "')")
            ->setFirstResult($paging_info->getOffset())
            ->setMaxResults($paging_info->getPerPage());

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $total     = $paginator->count();
        $data      = array();

        foreach($paginator as $entity)
            array_push($data, $entity);

        return new PagingResponse
        (
            $total,
            $paging_info->getPerPage(),
            $paging_info->getCurrentPage(),
            $paging_info->getLastPage($total),
            $data
        );
    }

    /**
     * @param int $event_id
     */
    public function cleanupAttendeesScheduleForEvent($event_id){

        $query = "DELETE SummitAttendee_Schedule FROM SummitAttendee_Schedule WHERE SummitEventID = {$event_id};";
        $this->_em->getConnection()->executeUpdate($query);
    }

    /**
     * @return string
     */
    protected function getBaseEntity()
    {
        return SummitEvent::class;
    }
}