<?php namespace models\oauth2;

    use models\main\Member;

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
 * Interface IResourceServerContext
 * Current Request OAUTH2 security context
 * @package oauth2
 */
interface IResourceServerContext
{

    /**
     * returns given scopes for current request
     * @return array
     */
    public function getCurrentScope();

    /**
     * gets current access token values
     * @return string
     */
    public function getCurrentAccessToken();

    /**
     * gets current access token lifetime
     * @return mixed
     */
    public function getCurrentAccessTokenLifetime();

    /**
     * gets current client id
     * @return string
     */
    public function getCurrentClientId();

    /**
     * gets current user id (if was set)
     * @return int|null
     */
    public function getCurrentUserId();

    /**
     * @return int|null
     */
    public function getCurrentUserExternalId();

    /**
     * @return string
     */
    public function getApplicationType();

    /**
     * @param array $auth_context
     * @return void
     */
    public function setAuthorizationContext(array $auth_context);

    /**
     * @return Member|null
     */
    public function getCurrentUser():?Member;

    /**
     * @return array
     */
    public function getCurrentUserGroups():array;
}