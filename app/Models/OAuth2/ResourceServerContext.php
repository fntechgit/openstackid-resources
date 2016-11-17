<?php namespace models\oauth2;

    /**
     * Copyright 2015 OpenStack Foundation
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

/**
 * Class ResourceServerContext
 * @package models\oauth2
 */
final class ResourceServerContext implements IResourceServerContext
{

    /**
     * @var array
     */
    private $auth_context;

    /**
     * @return array
     */
    public function getCurrentScope()
    {
        return isset($this->auth_context['scope']) ? explode(' ', $this->auth_context['scope']) : array();
    }

    /**
     * @return null|string
     */
    public function getCurrentAccessToken()
    {
        return isset($this->auth_context['access_token']) ? $this->auth_context['access_token'] : null;
    }


    /**
     * @return null|string
     */
    public function getCurrentAccessTokenLifetime()
    {
        return isset($this->auth_context['expires_in']) ? $this->auth_context['expires_in'] : null;
    }

    /**
     * @return null|string
     */
    public function getCurrentClientId()
    {
        return isset($this->auth_context['client_id']) ? $this->auth_context['client_id'] : null;
    }

    /**
     * @return null|int
     */
    public function getCurrentUserId()
    {
        return isset($this->auth_context['user_id']) ? intval($this->auth_context['user_id']) : null;
    }

    /**
     * @param array $auth_context
     * @return void
     */
    public function setAuthorizationContext(array $auth_context)
    {
        $this->auth_context = $auth_context;
    }

    /**
     * @return int
     */
    public function getCurrentUserExternalId()
    {
        return isset($this->auth_context['user_external_id']) ? intval($this->auth_context['user_external_id']) : null;
    }

    /**
     * @return string
     */
    public function getApplicationType()
    {
        return isset($this->auth_context['application_type']) ? $this->auth_context['application_type'] : null;
    }
}