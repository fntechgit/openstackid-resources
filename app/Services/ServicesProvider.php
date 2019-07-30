<?php namespace services;
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

use App\Http\Controllers\OAuth2SummitLocationsApiController;
use App\Http\Controllers\OAuth2SummitOrdersApiController;
use App\Permissions\IPermissionsManager;
use App\Permissions\PermissionsManager;
use App\Services\Apis\CalendarSync\ICalendarSyncRemoteFacadeFactory;
use App\Services\Apis\ExternalScheduleFeeds\ExternalScheduleFeedFactory;
use App\Services\Apis\ExternalScheduleFeeds\IExternalScheduleFeedFactory;
use App\Services\Apis\GoogleGeoCodingAPI;
use App\Services\Apis\IGeoCodingAPI;
use App\Services\Apis\IPaymentGatewayAPI;
use App\Services\Apis\IRegistrationPaymentGatewayAPI;
use App\Services\Apis\PaymentGateways\StripeApi;
use App\Services\Model\AttendeeService;
use App\Services\Model\FolderService;
use App\Services\Model\IAttendeeService;
use App\Services\Model\ICompanyService;
use App\Services\Model\IFolderService;
use App\Services\Model\ILocationService;
use App\Services\Model\IMemberService;
use App\Services\Model\Imp\CompanyService;
use App\Services\Model\Imp\RegistrationIngestionService;
use App\Services\Model\Imp\SponsorBadgeScanService;
use App\Services\Model\IOrganizationService;
use App\Services\Model\IPresentationCategoryGroupService;
use App\Services\Model\IRegistrationIngestionService;
use App\Services\Model\IRSVPTemplateService;
use App\Services\Model\IScheduleIngestionService;
use App\Services\Model\ISponsorBadgeScanService;
use App\Services\Model\ISponsorshipTypeService;
use App\Services\Model\ISummitAccessLevelTypeService;
use App\Services\Model\ISummitBadgeFeatureTypeService;
use App\Services\Model\ISummitBadgeTypeService;
use App\Services\Model\ISummitEventTypeService;
use App\Services\Model\ISummitOrderExtraQuestionTypeService;
use App\Services\Model\ISummitPushNotificationService;
use App\Services\Model\ISummitRefundPolicyTypeService;
use App\Services\Model\ISummitSelectionPlanService;
use App\Services\Model\ISummitTaxTypeService;
use App\Services\Model\ISummitTicketTypeService;
use App\Services\Model\ISummitTrackService;
use App\Services\Model\ISummitTrackTagGroupService;
use App\Services\Model\ITagService;
use App\Services\Model\ITrackQuestionTemplateService;
use App\Services\Model\OrganizationService;
use App\Services\Model\PresentationCategoryGroupService;
use App\Services\Model\ScheduleIngestionService;
use App\Services\Model\SponsorshipTypeService;
use App\Services\Model\SummitAccessLevelTypeService;
use App\Services\Model\SummitBadgeFeatureTypeService;
use App\Services\Model\SummitBadgeTypeService;
use App\Services\Model\SummitLocationService;
use App\Services\Model\MemberService;
use App\Services\Model\RSVPTemplateService;
use App\Services\Model\SummitOrderService;
use App\Services\Model\SummitPromoCodeService;
use App\Services\Model\SummitPushNotificationService;
use App\Services\Model\SummitSelectionPlanService;
use App\Services\Model\SummitTaxTypeService;
use App\Services\Model\SummitTicketTypeService;
use App\Services\Model\SummitTrackService;
use App\Services\Model\SummitTrackTagGroupService;
use App\Services\Model\TagService;
use App\Services\Model\TrackQuestionTemplateService;
use App\Services\SummitEventTypeService;
use App\Services\SummitOrderExtraQuestionTypeService;
use App\Services\SummitRefundPolicyTypeService;
use App\Services\SummitSponsorService;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use models\utils\SilverstripeBaseModel;
use ModelSerializers\BaseSerializerTypeSelector;
use ModelSerializers\ISerializerTypeSelector;
use services\apis\CalendarSync\CalendarSyncRemoteFacadeFactory;
use services\apis\EventbriteAPI;
use services\apis\FireBaseGCMApi;
use services\apis\IEventbriteAPI;
use services\apis\IPushNotificationApi;
use services\model\IPresentationService;
use services\model\ISpeakerService;
use services\model\ISummitPromoCodeService;
use libs\utils\ICacheService;
use services\model\ISummitService;
use services\model\ISummitSponsorService;
use services\model\PresentationService;
use services\model\SpeakerService;
use services\model\SummitService;
use services\utils\RedisCacheService;
use App\Services\Model\ISummitOrderService;
use App\Services\Apis\ExternalRegistrationFeeds\IExternalRegistrationFeedFactory;
use App\Services\Apis\ExternalRegistrationFeeds\ExternalRegistrationFeedFactory;
/***
 * Class ServicesProvider
 * @package services
 */
final class ServicesProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
    }

    public function register()
    {
        App::singleton(ClientInterface::class, Client::class);
        
        App::singleton(ICacheService::class, RedisCacheService::class);

        App::singleton(IPermissionsManager::class, PermissionsManager::class);

        App::singleton(\libs\utils\ITransactionService::class, function(){
            return new \services\utils\DoctrineTransactionService(SilverstripeBaseModel::EntityManager);
        });

        App::singleton(\libs\utils\IEncryptionService::class, function(){
            return new \services\utils\EncryptionService(
                Config::get("server.ss_encrypt_key", ''),
                Config::get("server.ss_encrypt_cypher", '')
            );
        });

        // setting facade

        App::singleton('encryption', function ($app) {
            return new \services\utils\EncryptionService(
                Config::get("server.ss_encrypt_key", ''),
                Config::get("server.ss_encrypt_cypher", '')
            );
        });

        App::singleton(ISerializerTypeSelector::class, BaseSerializerTypeSelector::class);

        App::singleton(ISummitService::class, SummitService::class);

        App::singleton(ISpeakerService::class, SpeakerService::class);

        App::singleton(IPresentationService::class, PresentationService::class);

        App::singleton('services\model\IChatTeamService', 'services\model\ChatTeamService');

        App::singleton(IEventbriteAPI::class,   function(){
            $api = new EventbriteAPI();
            $api->setCredentials(array('token' => Config::get("server.eventbrite_oauth2_personal_token", null)));
            return $api;
        });

        App::singleton(IPushNotificationApi::class,   function(){
            $api = new FireBaseGCMApi(Config::get("server.firebase_gcm_server_key", null));
            return $api;
        });

        App::singleton
        (
            IAttendeeService::class,
            AttendeeService::class
        );

        App::singleton
        (
            ICalendarSyncRemoteFacadeFactory::class,
            CalendarSyncRemoteFacadeFactory::class
        );

        // work request pre processors

        App::singleton
        (
            'App\Services\Model\Strategies\ICalendarSyncWorkRequestPreProcessorStrategyFactory',
            'App\Services\Model\Strategies\CalendarSyncWorkRequestPreProcessorStrategyFactory'
        );

        App::when('App\Services\Model\MemberActionsCalendarSyncPreProcessor')
            ->needs('App\Services\Model\ICalendarSyncWorkRequestQueueManager')
            ->give('App\Services\Model\MemberScheduleWorkQueueManager');

        App::when('App\Services\Model\AdminActionsCalendarSyncPreProcessor')
            ->needs('App\Services\Model\ICalendarSyncWorkRequestQueueManager')
            ->give('App\Services\Model\AdminScheduleWorkQueueManager');

        // work request process services

        App::when('App\Services\Model\MemberActionsCalendarSyncProcessingService')
            ->needs('App\Services\Model\ICalendarSyncWorkRequestPreProcessor')
            ->give('App\Services\Model\MemberActionsCalendarSyncPreProcessor');

        App::singleton
        (
            'App\Services\Model\IMemberActionsCalendarSyncProcessingService',
            'App\Services\Model\MemberActionsCalendarSyncProcessingService'
        );

        App::when('App\Services\Model\AdminActionsCalendarSyncProcessingService')
            ->needs('App\Services\Model\ICalendarSyncWorkRequestPreProcessor')
            ->give('App\Services\Model\AdminActionsCalendarSyncPreProcessor');

        App::singleton
        (
            'App\Services\Model\IAdminActionsCalendarSyncProcessingService',
            'App\Services\Model\AdminActionsCalendarSyncProcessingService'
        );

        App::singleton(
            IMemberService::class,
            MemberService::class
        );

        App::singleton
        (
            ISummitPromoCodeService::class,
            SummitPromoCodeService::class
        );

        App::singleton
        (
            ISummitEventTypeService::class,
            SummitEventTypeService::class
        );

        App::singleton
        (
            ISummitTrackService::class,
            SummitTrackService::class
        );

        App::singleton
        (
            ILocationService::class,
            SummitLocationService::class
        );

        App::singleton
        (
            IFolderService::class,
            FolderService::class
        );

        App::singleton
        (
            IRSVPTemplateService::class,
            RSVPTemplateService::class
        );

        App::singleton
        (
            ISummitTicketTypeService::class,
            SummitTicketTypeService::class
        );

        App::singleton
        (
            IPresentationCategoryGroupService::class,
            PresentationCategoryGroupService::class
        );

        App::singleton(
            ISummitPushNotificationService::class,
            SummitPushNotificationService::class
        );

        App::singleton(IGeoCodingAPI::class,   function(){
            return new GoogleGeoCodingAPI
            (
                Config::get("server.google_geocoding_api_key", null)
            );
        });

        App::singleton(
            ISummitSelectionPlanService::class,
            SummitSelectionPlanService::class
        );

        App::singleton(
            IOrganizationService::class,
            OrganizationService::class
        );

        App::singleton(
            ICompanyService::class,
            CompanyService::class
        );

        App::singleton(
            ISummitTrackTagGroupService::class,
            SummitTrackTagGroupService::class
        );

        App::singleton(
            ITrackQuestionTemplateService::class,
            TrackQuestionTemplateService::class
        );

        App::singleton(
            ITagService::class,
            TagService::class
        );

        App::singleton(
            IExternalScheduleFeedFactory::class,
            ExternalScheduleFeedFactory::class
        );

        App::singleton(
            IScheduleIngestionService::class,
            ScheduleIngestionService::class
        );

        App::singleton
        (
            ISummitAccessLevelTypeService::class,
            SummitAccessLevelTypeService::class
        );

        App::singleton
        (
            ISummitTaxTypeService::class,
            SummitTaxTypeService::class
        );

        App::singleton
        (
            ISummitBadgeFeatureTypeService::class,
            SummitBadgeFeatureTypeService::class
        );

        App::singleton
        (
            ISummitBadgeTypeService::class,
            SummitBadgeTypeService::class
        );

        App::singleton(
            ISummitSponsorService::class,
            SummitSponsorService::class
        );

        App::singleton(
            ISummitRefundPolicyTypeService::class,
            SummitRefundPolicyTypeService::class
        );

        App::singleton(
            ISummitOrderExtraQuestionTypeService::class,
            SummitOrderExtraQuestionTypeService::class
        );

        App::singleton(
            ISponsorshipTypeService::class,
            SponsorshipTypeService::class
        );

        App::singleton(ISummitOrderService::class, SummitOrderService::class);

        App::singleton(ISponsorBadgeScanService::class, SponsorBadgeScanService::class);

        // payment gateway

        // bookable rooms

        App::when(OAuth2SummitLocationsApiController::class)
        ->needs(IPaymentGatewayAPI::class)
        ->give(function(){
                return new StripeApi(
                    Config::get("stripe.booking_private_key", null),
                    Config::get("stripe.booking_endpoint_secret", null)
                );
        });

        App::when(SummitLocationService::class)
            ->needs(IPaymentGatewayAPI::class)
            ->give(function(){
                return new StripeApi(
                    Config::get("stripe.booking_private_key", null),
                    Config::get("stripe.booking_endpoint_secret", null)
                );
            });

        // summit registration

        App::when(OAuth2SummitOrdersApiController::class)
            ->needs(IPaymentGatewayAPI::class)
            ->give(function(){
                return new StripeApi(
                    Config::get("stripe.registration_private_key", null),
                    Config::get("stripe.registration_endpoint_secret", null)
                );
            });

        App::when(SummitOrderService::class)
            ->needs(IPaymentGatewayAPI::class)
            ->give(function(){
                return new StripeApi(
                    Config::get("stripe.registration_private_key", null),
                    Config::get("stripe.registration_endpoint_secret", null)
                );
            });


        App::singleton(IRegistrationPaymentGatewayAPI::class,   function(){
            return new StripeApi(
                Config::get("stripe.registration_private_key", null),
                Config::get("stripe.registration_endpoint_secret", null)
            );
        });

        App::singleton(
            IRegistrationIngestionService::class,
            RegistrationIngestionService::class
        );

        App::singleton(
            IExternalRegistrationFeedFactory::class,
            ExternalRegistrationFeedFactory::class
        );
    }

}