<?php namespace App\Jobs;
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
use App\Mail\RegisteredMemberOrderPaidMail;
use App\Mail\UnregisteredMemberOrderPaidMail;
use App\Models\Foundation\Summit\Repositories\ISummitOrderRepository;
use App\Services\Apis\ExternalUserApi;
use App\Services\Model\IMemberService;
use GuzzleHttp\Exception\ClientException;
use function GuzzleHttp\Psr7\str;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use libs\utils\ICacheService;
use libs\utils\ITransactionService;
use models\main\IMemberRepository;
use models\summit\SummitOrder;
/**
 * Class ProcessSummitOrderPaymentConfirmation
 * @package App\Jobs
 */
class ProcessSummitOrderPaymentConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries             = 5;
    const SkewTime            = 60;
    const AccessTokenCacheKey = 'REGISTRATION_SERVICE_OAUTH2_ACCESS_TOKEN';

    /**
     * @var int
     */
    private $order_id;

    /**
     * ProcessSummitOrderPaymentConfirmation constructor.
     * @param int $order_id
     */
    public function __construct(int $order_id)
    {
        $this->order_id = $order_id;
    }

    /**
     * @return \League\OAuth2\Client\Provider\GenericProvider
     */
    private function getIDPClient()
    {
        $client_id     = Config::get("registration.service_client_id");
        $client_secret = Config::get("registration.service_client_secret");
        $scopes        = Config::get("registration.service_client_scopes");

        Log::debug(sprintf("ProcessSummitOrderPaymentConfirmation::getIDPClient client_id %s client_secret %s scopes %s", $client_id, $client_secret, $scopes));

        $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => $client_id,
            'clientSecret'            => $client_secret,
            'redirectUri'             => "",
            'urlAuthorize'            => Config::get("idp.authorization_endpoint"),
            'urlAccessToken'          => Config::get("idp.token_endpoint"),
            'urlResourceOwnerDetails' => "",
            'scopes'                  => $scopes,
        ]);
        return $oauthClient;
    }

    private function sendAttendeesInvitationEmail(SummitOrder $order){
        Log::debug("ProcessSummitOrderPaymentConfirmation::sendAttendeesInvitationEmail");
        foreach ($order->getTickets() as $ticket){
            try {
                if (!$ticket->hasOwner()) {
                    Log::debug(sprintf("ProcessSummitOrderPaymentConfirmation::sendAttendeesInvitationEmail ticket %s has not owner set", $ticket->getNumber()));
                    continue;
                }
                $ticket->generateQRCode();
                $ticket->generateHash();
                $ticket->getOwner()->sendInvitationEmail($ticket);
            }
            catch (\Exception $ex){
                Log::warning($ex);
            }
        }
    }

    /**
     * @param SummitOrder $order
     */
    private function sendExistentSummitOrderOwnerEmail(SummitOrder $order){
        Log::debug("ProcessSummitOrderPaymentConfirmation::sendExistentSummitOrderOwnerEmail");
        Mail::queue(new RegisteredMemberOrderPaidMail($order));
    }

    /**
     * @param SummitOrder $order
     * @param array $user_registration_request
     */
    private function sendSummitOrderOwnerInvitationEmail(SummitOrder $order, array $user_registration_request){
        Log::debug("ProcessSummitOrderPaymentConfirmation::sendSummitOrderOwnerInvitationEmail");
        Mail::queue(new UnregisteredMemberOrderPaidMail($order, $user_registration_request['set_password_link']));
    }

    public function handle
    (
        ISummitOrderRepository $order_repository,
        IMemberRepository      $member_repository,
        IMemberService         $member_service,
        ITransactionService    $tx_service,
        ICacheService          $cache_service
    )
    {
        try{
            Log::debug("ProcessSummitOrderPaymentConfirmation::handle");
            $token = $cache_service->getSingleValue(self::AccessTokenCacheKey);
            if(empty($token)) {
                Log::debug("ProcessSummitOrderPaymentConfirmation::handle - access token is empty, getting new one");
                $client = $this->getIDPClient();
                // Try to get an access token using the client credentials grant.
                $accessToken = $client->getAccessToken('client_credentials',
                    [ 'scope' => Config::get("registration.service_client_scopes")]);
                $token = $accessToken->getToken();
                $expires_in = $accessToken->getExpires() - time();
                Log::debug(sprintf("ProcessSummitOrderPaymentConfirmation::handle - setting new access token %s", $token));
                $cache_service->setSingleValue(self::AccessTokenCacheKey, $token, $expires_in - self::SkewTime);
            }

            // get order

            $tx_service->transaction(function() use($order_repository, $member_repository, $token, $member_service){

                Log::debug(sprintf("ProcessSummitOrderPaymentConfirmation::handle - trying to get order id %s", $this->order_id));
                $order = $order_repository->getById($this->order_id);
                if(is_null($order) || !$order instanceof SummitOrder) return;

                Log::debug(sprintf("ProcessSummitOrderPaymentConfirmation::handle - got order nbr %s", $order->getNumber()));
                $order->generateQRCode();

                if(!$order->hasOwner()){
                    Log::debug("ProcessSummitOrderPaymentConfirmation::handle - order has not owner set");
                    $owner_email = $order->getOwnerEmail();
                    // check if we have a member on db
                    Log::debug(sprintf("ProcessSummitOrderPaymentConfirmation::handle - trying to get email %s from db", $owner_email));
                    $member = $member_repository->getByEmail($owner_email);

                    if(!is_null($member)){
                        Log::debug(sprintf("ProcessSummitOrderPaymentConfirmation::handle - member %s found at db", $owner_email));
                        $order->setOwner($member);

                        Log::debug("ProcessSummitOrderPaymentConfirmation::handle - sending email to owner");
                        // send email to owner;
                        $this->sendExistentSummitOrderOwnerEmail($order);

                        Log::debug("ProcessSummitOrderPaymentConfirmation::handle - sending email to attendees");
                        $this->sendAttendeesInvitationEmail($order);
                        return;
                    }

                    // check if user exists by email at idp
                    Log::debug(sprintf("ProcessSummitOrderPaymentConfirmation::handle - trying to get member %s from user api", $owner_email));
                    $user_api    = new ExternalUserApi($token);
                    $user        = $user_api->getUserByEmail($owner_email);
                    // check if primary email is the same if not disregard
                    $primary_email =  $user['email'] ?? null;
                    if(strcmp(strtolower($primary_email), strtolower($owner_email)) !== 0){
                        Log::debug(sprintf("ProcessSummitOrderPaymentConfirmation::handle primary email %s differs from order owner email %s", $primary_email, $owner_email));
                        // email are not equals , then is not the user bc primary emails differs ( could be a match on a secondary email)
                        $user = null; // set null on user and proceed to emit a registration request.
                    }
                    if(is_null($user)){
                        Log::debug(sprintf("ProcessSummitOrderPaymentConfirmation::handle - user %s does not exist on idp, emiting a registration request on idp", $owner_email));
                        // user does not exists , emit a registration request
                        $user_registration_request = $user_api->registerUser($owner_email, $order->getOwnerFirstName(), $order->getOwnerSurname());
                        // need to send email with set password link

                        $this->sendSummitOrderOwnerInvitationEmail($order, $user_registration_request);
                        $this->sendAttendeesInvitationEmail($order);
                        return;
                    }

                    Log::debug(sprintf("ProcessSummitOrderPaymentConfirmation::handle - Creating a local user for %s", $owner_email));
                    // we have an user on idp
                    $member = $member_service->registerExternalUser
                    (
                        $user['id'],
                        $user['email'],
                        $user['first_name'],
                        $user['last_name']
                    );
                    $member->addSummitRegistrationOrder($order);
                }
                // send email to owner
                $this->sendExistentSummitOrderOwnerEmail($order);
                // send email to owner;
                $this->sendAttendeesInvitationEmail($order);
            });
        }
        catch (ClientException $ex){
            Log::warning($ex);
            if($ex->getCode() == 401) {
                // invalid token
                $cache_service->delete(self::AccessTokenCacheKey);
            }
            throw $ex;
        }
        catch (\Exception $ex){
            Log::error($ex);
            throw $ex;
        }
    }
}
