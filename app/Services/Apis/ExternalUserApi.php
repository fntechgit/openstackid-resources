<?php namespace App\Services\Apis;
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
use GuzzleHttp\Client;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
/**
 * Class ExternalUserApi
 * @package App\Services\Apis
 */
final class ExternalUserApi
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var Client
     */
    private $client;

    /**
     * ExternalUserApi constructor.
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
        $this->client = new Client([
            'base_uri' => Config::get('idp.base_url', null),
            'timeout' => Config::get('curl.timeout', 60),
            'allow_redirects' => Config::get('curl.allow_redirects', false),
            'verify' => Config::get('curl.verify_ssl_cert', true)
        ]);
    }

    /**
     * @param string $email
     * @return null|mixed
     * @throws Exception
     */
    public function getUserByEmail(string $email)
    {
        try {
            $query = [
                'access_token' => $this->token
            ];

            $params = [
                'filter' => 'email==' . $email
            ];

            foreach ($params as $param => $value) {
                $query[$param] = $value;
            }

            $response = $this->client->get('/api/v1/users', [
                    'query' => $query,
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);

            return intval($data['total']) > 0 ? $data['data'][0] : null;
        }
        catch (Exception $ex) {
            Log::error($ex);
            throw $ex;
        }

    }

    /**
     * @param string $email
     * @param string $first_name
     * @param string $last_name
     * @return mixed
     * @throws Exception
     */
    public function registerUser(string $email, string $first_name, string $last_name)
    {
        try {
            $query = [
                'access_token' => $this->token
            ];

            $response = $this->client->post('/api/v1/user-registration-requests', [
                    'query' => $query,
                    RequestOptions::JSON => [
                        'email'      => $email,
                        'first_name' => $first_name,
                        'last_name'  => $last_name,
                    ]
                ]
            );
            return json_decode($response->getBody()->getContents(), true);
        } catch (Exception $ex) {
            Log::error($ex);
            throw $ex;
        }
    }

}