<?php namespace services\apis;
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
use GuzzleHttp\Client;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use models\summit\Summit;
/**
 * Class EventbriteAPI
 * @package services\apis
 */
final class EventbriteAPI implements IEventbriteAPI
{

    const BaseUrl = 'https://www.eventbriteapi.com/v3';

    private $auth_info;
    /**
     * @param array $auth_info
     * @return $this
     */
    public function setCredentials(array $auth_info)
    {
        $this->auth_info = $auth_info;
    }

    /**
     * @param string $api_url
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function getEntity($api_url, array $params)
    {
        try {
            if (strstr($api_url, self::BaseUrl) === false)
                throw new Exception('invalid base url!');

            $client = new Client();

            $query = [
                'token' => $this->auth_info['token']
            ];

            foreach ($params as $param => $value) {
                $query[$param] = $value;
            }

            $response = $client->get($api_url, [
                    'query' => $query
                ]
            );

            if ($response->getStatusCode() !== 200)
                throw new Exception('invalid status code!');

            $content_type = $response->getHeaderLine('content-type');

            if (empty($content_type))
                throw new Exception('invalid content type!');

            if ($content_type !== 'application/json')
                throw new Exception('invalid content type!');

            $json = $response->getBody()->getContents();
            return json_decode($json, true);
        }
        catch(RequestException $ex){
            Log::warning($ex->getMessage());
            throw $ex;
        }
    }

    /**
     * @param string $order_id
     * @return mixed
     */
    public function getOrder($order_id)
    {
        $order_id = intval($order_id);
        $url      = sprintf('%s/orders/%s', self::BaseUrl, $order_id);
        return $this->getEntity($url, ['expand' => 'attendees']);
    }

    /**
     * @param Summit $summit
     * @return mixed
     */
    public function getTicketTypes(Summit $summit){
        $event_id = $summit->getExternalSummitId();
        $url      = sprintf('%s/events/%s', self::BaseUrl, $event_id);
        return $this->getEntity($url, ['expand' => 'ticket_classes']);
    }

    /**
     * @param Summit $summit
     * @param int $page_nbr
     * @param string $expand
     * @return mixed
     */
    public function getAttendees(Summit $summit, $page_nbr, $expand = 'promotional_code'){
        $event_id = $summit->getExternalSummitId();
        $url      = sprintf('%s/events/%s/attendees', self::BaseUrl, $event_id);
        return $this->getEntity($url, ['expand' => $expand, 'page' => $page_nbr]);
    }
}