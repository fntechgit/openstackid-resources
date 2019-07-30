<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

require app_path('Http/Routes/public.php');

//OAuth2 Protected API
Route::group([
    'namespace' => 'App\Http\Controllers',
    'prefix'     => 'api/v1',
    'before'     => [],
    'after'      => [],
    'middleware' => ['ssl', 'oauth2.protected', 'rate.limit','etags']
], function () {

    // members
    Route::group(['prefix'=>'members'], function(){
        Route::get('', 'OAuth2MembersApiController@getAll');

        Route::group(['prefix'=>'me'], function(){
            // get my member info
            Route::get('', 'OAuth2MembersApiController@getMyMember');

            // my affiliations
            Route::group(['prefix' => 'affiliations'], function(){
                Route::get('', [  'uses' => 'OAuth2MembersApiController@getMyMemberAffiliations']);
                Route::post('', [ 'uses' => 'OAuth2MembersApiController@addMyAffiliation']);
                Route::group(['prefix' => '{affiliation_id}'], function(){
                    Route::put('',  ['uses' => 'OAuth2MembersApiController@updateMyAffiliation']);
                    Route::delete('', [  'uses' => 'OAuth2MembersApiController@deleteMyAffiliation']);
                });
            });
        });

        Route::group(['prefix'=>'{member_id}'], function(){

            Route::group(['prefix' => 'affiliations'], function(){
                Route::get('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2MembersApiController@getMemberAffiliations']);
                Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2MembersApiController@addAffiliation']);
                Route::group(['prefix' => '{affiliation_id}'], function(){
                    Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2MembersApiController@updateAffiliation']);
                    Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2MembersApiController@deleteAffiliation']);
                });
            });

            Route::group(array('prefix' => 'rsvp'), function () {
                Route::group(['prefix' => '{rsvp_id}'], function () {
                    Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2MembersApiController@deleteRSVP']);
                });
            });
        });
    });

    // tags
    Route::group(['prefix'=>'tags'], function(){
        Route::get('', 'OAuth2TagsApiController@getAll');
        Route::post('',  [ 'middleware' => 'auth.user', 'uses' => 'OAuth2TagsApiController@addTag']);
    });

    // companies
    Route::group(['prefix'=>'companies'], function(){
        Route::get('', 'OAuth2CompaniesApiController@getAllCompanies');
        Route::post('', 'OAuth2CompaniesApiController@add');
    });

    // organizations
    Route::group(['prefix'=>'organizations'], function(){
        Route::get('', 'OAuth2OrganizationsApiController@getAll');
        Route::post('', 'OAuth2OrganizationsApiController@addOrganization');
    });

    // groups
    Route::group(['prefix'=>'groups'], function(){
        Route::get('', 'OAuth2GroupsApiController@getAll');
    });

    // summits
    Route::group(array('prefix' => 'summits'), function () {

        Route::get('',  [ 'middleware' => 'cache:'.Config::get('cache_api_response.get_summits_response_lifetime', 600), 'uses' => 'OAuth2SummitApiController@getSummits']);

        Route::group(['prefix' => 'all'], function () {

            Route::get('', 'OAuth2SummitApiController@getAllSummits');
            Route::get('{id}',  'OAuth2SummitApiController@getAllSummitByIdOrSlug');
            Route::group(['prefix' => 'selection-plans'], function () {
                Route::get('current/{status}', ['uses' => 'OAuth2SummitSelectionPlansApiController@getCurrentSelectionPlanByStatus'])->where('status', 'submission|selection|voting');
            });
            Route::group(['prefix' => 'locations'], function () {
                // GET /api/v1/summits/all/locations/bookable-rooms/all/reservations/{id}
                Route::get('bookable-rooms/all/reservations/{id}', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@getReservationById']);
            });

            Route::group(['prefix' => 'orders'], function () {
                Route::get('me',  'OAuth2SummitOrdersApiController@getAllMyOrders');

                Route::group(['prefix' => 'all'], function () {
                    Route::group(['prefix' => 'tickets'], function () {
                        Route::group(['prefix' => '{ticket_id}'], function () {
                            Route::put('',  'OAuth2SummitOrdersApiController@updateTicketById');
                            Route::get('pdf',  'OAuth2SummitOrdersApiController@getTicketPDFById');
                        });

                        Route::group(['prefix' => 'me'], function () {
                            Route::get('',  'OAuth2SummitTicketApiController@getAllMyTickets');
                        });
                    });
                });

                Route::group(['prefix' => '{order_id}'], function () {
                    Route::delete('refund',  'OAuth2SummitOrdersApiController@requestRefundMyOrder');
                    Route::put('', 'OAuth2SummitOrdersApiController@updateMyOrder');
                    Route::group(['prefix' => 'tickets'], function () {
                        Route::group(['prefix' => '{ticket_id}'], function () {
                            Route::get('pdf',  'OAuth2SummitOrdersApiController@getTicketPDFByOrderId');
                            Route::delete('refund',  'OAuth2SummitOrdersApiController@requestRefundMyTicket');
                            Route::group(['prefix' => 'attendee'], function () {
                                Route::put('',  'OAuth2SummitOrdersApiController@assignAttendee');
                                Route::put('reinvite',  'OAuth2SummitOrdersApiController@reInviteAttendee');
                                Route::delete('',  'OAuth2SummitOrdersApiController@removeAttendee');
                            });
                        });
                    });
                });

            });
        });

        Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitApiController@addSummit']);

        Route::group(['prefix' => '{id}'], function () {
            Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitApiController@updateSummit']);
            Route::post('logo', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitApiController@addSummitLogo']);
            Route::delete('logo', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitApiController@deleteSummitLogo']);
            Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitApiController@deleteSummit']);
            Route::get('', [ 'middleware' => 'cache:'.Config::get('cache_api_response.get_summit_response_lifetime', 1200), 'uses' => 'OAuth2SummitApiController@getSummit'])->where('id', 'current|[0-9]+');

            // selection plans
            Route::group(['prefix' => 'selection-plans'], function () {
                Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitSelectionPlansApiController@addSelectionPlan']);
                Route::group(['prefix' => '{selection_plan_id}'], function () {
                    Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitSelectionPlansApiController@updateSelectionPlan']);
                    Route::get('', ['uses' => 'OAuth2SummitSelectionPlansApiController@getSelectionPlan']);
                    Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitSelectionPlansApiController@deleteSelectionPlan']);
                    Route::group(['prefix' => 'track-groups'], function () {
                        Route::put('{track_group_id}', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitSelectionPlansApiController@addTrackGroupToSelectionPlan']);
                        Route::delete('{track_group_id}', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitSelectionPlansApiController@deleteTrackGroupToSelectionPlan']);
                    });
                });
            });

            // RSVP templates
            Route::group(['prefix' => 'rsvp-templates'], function () {
                Route::get('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@getAllBySummit']);

                Route::group(['prefix' => 'questions'], function () {
                    Route::get('metadata', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@getRSVPTemplateQuestionsMetadata']);
                });

                Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@addRSVPTemplate']);
                Route::group(['prefix' => '{template_id}'], function () {
                    Route::get('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@getRSVPTemplate']);
                    Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@updateRSVPTemplate']);
                    Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@deleteRSVPTemplate']);
                    Route::group(['prefix' => 'questions'], function () {
                        Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@addRSVPTemplateQuestion']);
                        Route::group(['prefix' => '{question_id}'], function () {
                            Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@updateRSVPTemplateQuestion']);
                            Route::get('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@getRSVPTemplateQuestion']);
                            Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@deleteRSVPTemplateQuestion']);
                            // multi values questions
                            Route::group(['prefix' => 'values'], function () {
                                Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@addRSVPTemplateQuestionValue']);
                                Route::group(['prefix' => '{value_id}'], function () {
                                    Route::get('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@getRSVPTemplateQuestionValue']);
                                    Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@updateRSVPTemplateQuestionValue']);
                                    Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitRSVPTemplatesApiController@deleteRSVPTemplateQuestionValue']);
                                });
                            });
                        });
                    });
                });
            });

            Route::get('entity-events', 'OAuth2SummitApiController@getSummitEntityEvents');

            // notifications
            Route::group(['prefix' => 'notifications'], function () {
                Route::get('sent', 'OAuth2SummitNotificationsApiController@getAllApprovedByUser');
                Route::get('', [ 'middleware' => 'auth.user', 'uses' =>  'OAuth2SummitNotificationsApiController@getAll']);
                Route::get('csv', [ 'middleware' => 'auth.user', 'uses' =>  'OAuth2SummitNotificationsApiController@getAllCSV']);
                Route::post('', [ 'middleware' => 'auth.user', 'uses' =>  'OAuth2SummitNotificationsApiController@addPushNotification']);
                Route::group(['prefix' => '{notification_id}'], function () {
                    Route::get('', [ 'middleware' => 'auth.user', 'uses' =>  'OAuth2SummitNotificationsApiController@getById']);
                    Route::put('approve', [ 'middleware' => 'auth.user', 'uses' =>  'OAuth2SummitNotificationsApiController@approveNotification']);
                    Route::delete('approve', [ 'middleware' => 'auth.user', 'uses' =>  'OAuth2SummitNotificationsApiController@unApproveNotification']);
                    Route::delete('', [ 'middleware' => 'auth.user', 'uses' =>  'OAuth2SummitNotificationsApiController@deleteNotification']);
                });
            });

            // speakers
            Route::group(['prefix' => 'speakers'], function () {

                Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitSpeakersApiController@addSpeakerBySummit']);
                Route::get('', 'OAuth2SummitSpeakersApiController@getSpeakers');
                Route::get('me', 'OAuth2SummitSpeakersApiController@getMySummitSpeaker');

                Route::group(['prefix' => '{speaker_id}'], function () {
                    Route::get('', 'OAuth2SummitSpeakersApiController@getSummitSpeaker')->where('speaker_id', '[0-9]+');
                    Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitSpeakersApiController@updateSpeakerBySummit'])->where('speaker_id', 'me|[0-9]+');
                });
            });

            // speakers assistance
            Route::group(['prefix' => 'speakers-assistances'], function () {
                Route::get('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitSpeakersAssistanceApiController@getBySummit']);
                Route::get('csv', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitSpeakersAssistanceApiController@getBySummitCSV']);
                Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitSpeakersAssistanceApiController@addSpeakerSummitAssistance']);
                Route::group(['prefix' => '{assistance_id}'], function () {
                    Route::get('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitSpeakersAssistanceApiController@getSpeakerSummitAssistanceBySummit']);
                    Route::delete('',[ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitSpeakersAssistanceApiController@deleteSpeakerSummitAssistance']);
                    Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitSpeakersAssistanceApiController@updateSpeakerSummitAssistance']);
                    Route::post('mail', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitSpeakersAssistanceApiController@sendSpeakerSummitAssistanceAnnouncementMail']);
                });
            });

            // events
            Route::group(array('prefix' => 'events'), function () {

                Route::get('',[ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitEventsApiController@getEvents']);
                Route::get('csv',[ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitEventsApiController@getEventsCSV']);
                // bulk actions
                Route::delete('/publish', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitEventsApiController@unPublishEvents']);
                Route::put('/publish', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitEventsApiController@updateAndPublishEvents']);
                Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitEventsApiController@updateEvents']);

                Route::group(array('prefix' => 'unpublished'), function () {
                    Route::get('', 'OAuth2SummitEventsApiController@getUnpublishedEvents');
                    //Route::get('{event_id}', 'OAuth2SummitEventsApiController@getUnpublisedEvent');
                });

                Route::group(array('prefix' => 'published'), function () {
                    Route::get('', 'OAuth2SummitEventsApiController@getScheduledEvents');
                    Route::get('/empty-spots', 'OAuth2SummitEventsApiController@getScheduleEmptySpots');
                });

                Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitEventsApiController@addEvent']);
                Route::group(array('prefix' => '{event_id}'), function () {

                    Route::post('/clone',  [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitEventsApiController@cloneEvent']);
                    Route::get('', 'OAuth2SummitEventsApiController@getEvent');
                    Route::get('/published', [ 'middleware' => 'cache:'.Config::get('cache_api_response.get_published_event_response_lifetime', 300), 'uses' => 'OAuth2SummitEventsApiController@getScheduledEvent']);
                    Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitEventsApiController@updateEvent' ]);
                    Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitEventsApiController@deleteEvent' ]);
                    Route::put('/publish', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitEventsApiController@publishEvent']);
                    Route::delete('/publish', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitEventsApiController@unPublishEvent']);
                    Route::post('/feedback', 'OAuth2SummitEventsApiController@addEventFeedback');
                    Route::post('/attachment', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitEventsApiController@addEventAttachment']);
                    Route::get('/feedback/{attendee_id?}',  ['middleware' => 'cache:'.Config::get('cache_api_response.get_event_feedback_response_lifetime', 300), 'uses' => 'OAuth2SummitEventsApiController@getEventFeedback'] )->where('attendee_id', 'me|[0-9]+');
                });
            });

            // presentations
            Route::group(['prefix' => 'presentations'], function () {
                // opened without role CFP - valid selection plan on CFP status
                Route::post('', 'OAuth2PresentationApiController@submitPresentation');

                Route::group(['prefix' => '{presentation_id}'], function () {

                    // opened without role CFP - valid selection plan on CFP status
                    Route::put('', 'OAuth2PresentationApiController@updatePresentationSubmission');

                    Route::put('completed', 'OAuth2PresentationApiController@completePresentationSubmission');

                    Route::delete('', 'OAuth2PresentationApiController@deletePresentation');

                    // videos
                    Route::group(['prefix' => 'videos'], function () {
                        Route::get('', 'OAuth2PresentationApiController@getPresentationVideos');
                        Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2PresentationApiController@addVideo' ]);
                        Route::group(['prefix' => '{video_id}'], function () {
                            Route::get('', 'OAuth2PresentationApiController@getPresentationVideo');
                            Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2PresentationApiController@updateVideo' ]);
                            Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2PresentationApiController@deleteVideo' ]);
                        });
                    });

                    // slides
                    Route::group(['prefix' => 'slides'], function () {
                        Route::get('', 'OAuth2PresentationApiController@getPresentationSlides');
                        Route::post('', 'OAuth2PresentationApiController@addPresentationSlide' );
                        Route::group(['prefix' => '{slide_id}'], function () {
                            Route::get('', 'OAuth2PresentationApiController@getPresentationSlide');
                            Route::put('', 'OAuth2PresentationApiController@updatePresentationSlide' );
                            Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2PresentationApiController@deletePresentationSlide' ]);
                        });
                    });

                    // links
                    Route::group(['prefix' => 'links'], function () {
                        Route::get('', 'OAuth2PresentationApiController@getPresentationLinks');
                        Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2PresentationApiController@addPresentationLink' ]);
                        Route::group(['prefix' => '{link_id}'], function () {
                            Route::get('', 'OAuth2PresentationApiController@getPresentationLink');
                            Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2PresentationApiController@updatePresentationLink' ]);
                            Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2PresentationApiController@deletePresentationLink' ]);
                        });
                    });
                });
            });

            // locations
            Route::group(['prefix' => 'locations'], function () {

                Route::get('', 'OAuth2SummitLocationsApiController@getLocations');
                Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@addLocation']);

                Route::get('metadata', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@getMetadata']);

                // bookable-rooms
                Route::group(['prefix' => 'bookable-rooms'], function () {
                    // GET /api/v1/summits/{id}/locations/bookable-rooms
                    Route::get('', 'OAuth2SummitLocationsApiController@getBookableVenueRooms');

                    Route::group(['prefix' => 'all'], function () {
                        Route::group(['prefix' => 'reservations'], function () {
                            // GET /api/v1/summits/{id}/locations/bookable-rooms/all/reservations
                            Route::get('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@getAllReservationsBySummit']);
                            // GET /api/v1/summits/{id}/locations/bookable-rooms/all/reservations/csv
                            Route::get('csv', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@getAllReservationsBySummitCSV']);
                            // GET /api/v1/summits/{id}/locations/bookable-rooms/all/reservations/me
                            Route::get('me', 'OAuth2SummitLocationsApiController@getMyBookableVenueRoomReservations');
                            Route::group(['prefix' => '{reservation_id}'], function () {
                                // DELETE /api/v1/summits/{id}/locations/bookable-rooms/all/reservations/{reservation_id}
                                Route::delete('', 'OAuth2SummitLocationsApiController@cancelMyBookableVenueRoomReservation');
                            });
                        });
                    });

                    Route::group(['prefix' => '{room_id}'], function () {
                        // GET /api/v1/summits/{id}/locations/bookable-rooms/{room_id}
                        Route::get('', 'OAuth2SummitLocationsApiController@getBookableVenueRoom');
                        // GET /api/v1/summits/{id}/locations/bookable-rooms/{room_id}/availability/{day}
                        Route::get('availability/{day}', 'OAuth2SummitLocationsApiController@getBookableVenueRoomAvailability');

                        Route::group(['prefix' => 'reservations'], function () {
                            // POST /api/v1/summits/{id}/locations/bookable-rooms/{room_id}/reservations
                            Route::post('', 'OAuth2SummitLocationsApiController@createBookableVenueRoomReservation');

                            Route::group(['prefix' => '{reservation_id}'], function () {
                                // GET /api/v1/summits/{id}/bookable-rooms/{room_id}/reservations/{reservation_id}
                                Route::get('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@getBookableVenueRoomReservation']);
                                // DELETE /api/v1/summits/{id}/bookable-rooms/{room_id}/reservations/{reservation_id}/refund
                                Route::delete('refund', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@refundBookableVenueRoomReservation']);
                            });
                        });

                    });
                });

                // venues

                Route::group(['prefix' => 'venues'], function () {

                    Route::get('', 'OAuth2SummitLocationsApiController@getVenues');
                    Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@addVenue']);

                    Route::group(['prefix' => '{venue_id}'], function () {
                        Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@updateVenue']);

                        Route::group(['prefix' => 'rooms'], function () {
                            Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@addVenueRoom']);
                            Route::group(['prefix' => '{room_id}'], function () {
                                Route::get('', 'OAuth2SummitLocationsApiController@getVenueRoom');
                                Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@updateVenueRoom']);
                                Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@deleteVenueRoom']);
                                Route::group(['prefix' => 'image'], function () {
                                    Route::post('',  [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@addVenueRoomImage']);
                                    Route::delete('',  [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@removeVenueRoomImage']);
                                });
                            });
                        });

                        // bookable-rooms
                        Route::group(['prefix' => 'bookable-rooms'], function () {
                            // POST /api/v1/summits/{id}/locations/venues/{venue_id}/bookable-rooms
                            Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@addVenueBookableRoom']);
                            Route::group(['prefix' => '{room_id}'], function () {
                                // GET /api/v1/summits/{id}/locations/venues/{venue_id}/bookable-rooms/{room_id}
                                Route::get('', 'OAuth2SummitLocationsApiController@getBookableVenueRoomByVenue');
                                // PUT /api/v1/summits/{id}/locations/venues/{venue_id}/bookable-rooms/{room_id}
                                Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@updateVenueBookableRoom']);
                                // DELETE /api/v1/summits/{id}/locations/venues/{venue_id}/bookable-rooms/{room_id}
                                Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@deleteVenueBookableRoom']);
                                // attributes

                                Route::group(['prefix' => 'attributes'], function () {
                                    Route::group(['prefix' => '{attribute_id}'], function () {
                                        // PUT /api/v1/summits/{id}/locations/venues/{venue_id}/bookable-rooms/{room_id}/attributes/{attribute_id}
                                        Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@addVenueBookableRoomAttribute']);
                                        // DELETE /api/v1/summits/{id}/locations/venues/{venue_id}/bookable-rooms/{room_id}/attributes/{attribute_id}
                                        Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@deleteVenueBookableRoomAttribute']);
                                    });

                                });
                            });
                        });

                        Route::group(['prefix' => 'floors'], function () {
                            Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@addVenueFloor']);
                            Route::group(['prefix' => '{floor_id}'], function () {
                                Route::get('', 'OAuth2SummitLocationsApiController@getVenueFloor');
                                Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@updateVenueFloor']);
                                Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@deleteVenueFloor']);
                                Route::group(['prefix' => 'image'], function () {
                                    Route::post('',  [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@addVenueFloorImage']);
                                    Route::delete('',  [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@removeVenueFloorImage']);
                                });
                                Route::group(['prefix' => 'rooms'], function () {
                                    Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@addVenueFloorRoom']);
                                    Route::group(['prefix' => '{room_id}'], function () {
                                        Route::get('', 'OAuth2SummitLocationsApiController@getVenueFloorRoom');
                                        Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@updateVenueFloorRoom']);
                                    });
                                });
                                Route::group(['prefix' => 'bookable-rooms'], function () {
                                    Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@addVenueFloorBookableRoom']);
                                    Route::group(['prefix' => '{room_id}'], function () {
                                        Route::get('', 'OAuth2SummitLocationsApiController@getVenueFloorBookableRoom');
                                        Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@updateVenueFloorBookableRoom']);
                                    });
                                });
                            });
                        });
                    });
                });

                Route::group(['prefix' => 'airports'], function () {
                    Route::get('', 'OAuth2SummitLocationsApiController@getAirports');
                    Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@addAirport']);
                    Route::group(['prefix' => '{airport_id}'], function () {
                        Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@updateAirport']);
                    });
                });

                Route::group(['prefix' => 'hotels'], function () {
                    Route::get('', 'OAuth2SummitLocationsApiController@getHotels');
                    Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@addExternalLocation']);
                    Route::group(['prefix' => '{hotel_id}'], function () {
                        Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@updateExternalLocation']);
                    });
                });

                Route::group(['prefix' => 'external-locations'], function () {
                    Route::get('', 'OAuth2SummitLocationsApiController@getExternalLocations');
                    Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@addHotel']);
                    Route::group(['prefix' => '{external_location_id}'], function () {
                        Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@updateHotel']);
                    });
                });

                Route::group(['prefix' => '{location_id}'], function () {
                    Route::get('', 'OAuth2SummitLocationsApiController@getLocation');

                    // locations maps
                    Route::group(['prefix' => 'maps'], function () {
                        Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@addLocationMap']);
                        Route::group(['prefix' => '{map_id}'], function () {
                            Route::get('', 'OAuth2SummitLocationsApiController@getLocationMap');
                            Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@updateLocationMap']);
                            Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@deleteLocationMap']);
                        });
                    });

                    // locations images
                    Route::group(['prefix' => 'images'], function () {
                        Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@addLocationImage']);
                        Route::group(['prefix' => '{image_id}'], function () {
                            Route::get('', 'OAuth2SummitLocationsApiController@getLocationImage');
                            Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@updateLocationImage']);
                            Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@deleteLocationImage']);
                        });
                    });

                    Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@updateLocation']);
                    Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@deleteLocation']);
                    Route::get('/events/published','OAuth2SummitLocationsApiController@getLocationPublishedEvents')->where('location_id', 'tbd|[0-9]+');
                    Route::get('/events','OAuth2SummitLocationsApiController@getLocationEvents')->where('location_id', 'tbd|[0-9]+');
                    // location banners
                    Route::group(['prefix' => 'banners'], function () {
                        Route::get('', 'OAuth2SummitLocationsApiController@getLocationBanners');
                        Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@addLocationBanner']);
                        Route::group(['prefix' => '{banner_id}'], function () {
                            Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@updateLocationBanner']);
                            Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitLocationsApiController@deleteLocationBanner']);
                        });
                    });
                });
            });

            // bookable rooms attributes
            Route::group(['prefix' => 'bookable-room-attribute-types'], function () {
                Route::get('', 'OAuth2SummitBookableRoomsAttributeTypeApiController@getAllBookableRoomAttributeTypes');
                Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitBookableRoomsAttributeTypeApiController@addBookableRoomAttributeType']);
                Route::group(['prefix' => '{type_id}'], function () {
                    Route::get('', 'OAuth2SummitBookableRoomsAttributeTypeApiController@getBookableRoomAttributeType');
                    Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitBookableRoomsAttributeTypeApiController@updateBookableRoomAttributeType']);
                    Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitBookableRoomsAttributeTypeApiController@deleteBookableRoomAttributeType']);
                    Route::group(['prefix' => 'values'], function () {
                        Route::get('', 'OAuth2SummitBookableRoomsAttributeTypeApiController@getAllBookableRoomAttributeValues');
                        Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitBookableRoomsAttributeTypeApiController@addBookableRoomAttributeValue']);
                        Route::group(['prefix' => '{value_id}'], function () {
                            Route::get('', 'OAuth2SummitBookableRoomsAttributeTypeApiController@getBookableRoomAttributeValue');
                            Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitBookableRoomsAttributeTypeApiController@updateBookableRoomAttributeValue']);
                            Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitBookableRoomsAttributeTypeApiController@deleteBookableRoomAttributeValue']);
                        });
                    });
                });
            });

            // event types
            Route::group(['prefix' => 'event-types'], function () {
                Route::get('', 'OAuth2SummitsEventTypesApiController@getAllBySummit');
                Route::get('csv', 'OAuth2SummitsEventTypesApiController@getAllBySummitCSV');
                Route::post('seed-defaults', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitsEventTypesApiController@seedDefaultEventTypesBySummit']);
                Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitsEventTypesApiController@addEventTypeBySummit']);
                Route::group(['prefix' => '{event_type_id}'], function () {
                    Route::get('', 'OAuth2SummitsEventTypesApiController@getEventTypeBySummit');
                    Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitsEventTypesApiController@updateEventTypeBySummit']);
                    Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitsEventTypesApiController@deleteEventTypeBySummit']);
                });
            });

            // ticket types
            Route::group(['prefix' => 'ticket-types'], function () {
                Route::get('', 'OAuth2SummitsTicketTypesApiController@getAllBySummit');
                Route::get('csv', 'OAuth2SummitsTicketTypesApiController@getAllBySummitCSV');
                Route::post('seed-defaults', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitsTicketTypesApiController@seedDefaultTicketTypesBySummit']);
                Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitsTicketTypesApiController@addTicketTypeBySummit']);
                Route::group(['prefix' => '{ticket_type_id}'], function () {
                    Route::get('', 'OAuth2SummitsTicketTypesApiController@getTicketTypeBySummit');
                    Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitsTicketTypesApiController@updateTicketTypeBySummit']);
                    Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitsTicketTypesApiController@deleteTicketTypeBySummit']);
                });
            });

            // begin registration endpoints

            // tax-types
            Route::group(['prefix' => 'tax-types'], function () {
                Route::get('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitTaxTypeApiController@getAllBySummit']);
                Route::post('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitTaxTypeApiController@add']);
                Route::group(['prefix' => '{tax_id}'], function () {
                    Route::get('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitTaxTypeApiController@get']);
                    Route::put('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitTaxTypeApiController@update']);
                    Route::delete('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitTaxTypeApiController@delete']);

                    Route::group(['prefix' => 'ticket-types'], function () {
                        Route::group(['prefix' => '{ticket_type_id}'], function () {
                            Route::put('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitTaxTypeApiController@addTaxToTicketType']);
                            Route::delete('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitTaxTypeApiController@removeTaxFromTicketType']);
                        });
                    });
                });
            });

            // refund-policies
            Route::group(['prefix' => 'refund-policies'], function () {
                Route::get('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitRefundPolicyTypeApiController@getAllBySummit']);
                Route::post('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitRefundPolicyTypeApiController@add']);
                Route::group(['prefix' => '{policy_id}'], function () {
                    Route::get('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitRefundPolicyTypeApiController@get']);
                    Route::put('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitRefundPolicyTypeApiController@update']);
                    Route::delete('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitRefundPolicyTypeApiController@delete']);
                });
            });

            // sponsors
            Route::group(['prefix' => 'sponsors'], function () {
                Route::get('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitSponsorApiController@getAllBySummit']);
                Route::post('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitSponsorApiController@add']);
                Route::group(['prefix' => '{sponsor_id}'], function () {
                    Route::get('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitSponsorApiController@get']);
                    Route::put('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitSponsorApiController@update']);
                    Route::delete('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitSponsorApiController@delete']);
                    Route::group(['prefix' => 'users'], function () {
                        Route::group(['prefix' => '{member_id}'], function () {
                            Route::put('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitSponsorApiController@addSponsorUser']);
                            Route::delete('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitSponsorApiController@removeSponsorUser']);
                        });
                    });
                });
            });

            // order-extra-questions
            Route::group(['prefix' => 'order-extra-questions'], function () {
                Route::get('metadata', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitOrderExtraQuestionTypeApiController@getMetadata']);
                Route::get('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitOrderExtraQuestionTypeApiController@getAllBySummit']);
                Route::post('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitOrderExtraQuestionTypeApiController@add']);
                Route::group(['prefix' => '{question_id}'], function () {
                    Route::get('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitOrderExtraQuestionTypeApiController@get']);
                    Route::put('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitOrderExtraQuestionTypeApiController@update']);
                    Route::delete('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitOrderExtraQuestionTypeApiController@delete']);
                    Route::group(['prefix' => 'values'], function () {
                        Route::post('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitOrderExtraQuestionTypeApiController@addQuestionValue']);
                        Route::group(['prefix' => '{value_id}'], function () {
                            Route::put('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitOrderExtraQuestionTypeApiController@updateQuestionValue']);
                            Route::delete('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitOrderExtraQuestionTypeApiController@deleteQuestionValue']);
                        });
                    });
                });
            });

            // access-levels
            Route::group(['prefix' => 'access-level-types'], function () {
                Route::get('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitAccessLevelTypeApiController@getAllBySummit']);
                Route::post('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitAccessLevelTypeApiController@add']);
                Route::group(['prefix' => '{level_id}'], function () {
                    Route::get('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitAccessLevelTypeApiController@get']);
                    Route::put('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitAccessLevelTypeApiController@update']);
                    Route::delete('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitAccessLevelTypeApiController@delete']);
                });
            });

            // badge-feature-types
            Route::group(['prefix' => 'badge-feature-types'], function () {
                Route::get('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitBadgeFeatureTypeApiController@getAllBySummit']);
                Route::post('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitBadgeFeatureTypeApiController@add']);
                Route::group(['prefix' => '{feature_id}'], function () {
                    Route::get('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitBadgeFeatureTypeApiController@get']);
                    Route::put('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitBadgeFeatureTypeApiController@update']);
                    Route::delete('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitBadgeFeatureTypeApiController@delete']);
                });
            });

            // badge-feature-types
            Route::group(['prefix' => 'badge-scans'], function () {

                Route::get('me','OAuth2SummitBadgeScanApiController@getAllMyBadgeScans' );
                Route::get('', 'OAuth2SummitBadgeScanApiController@getAllBySummit');
                Route::get('csv','OAuth2SummitBadgeScanApiController@getAllBySummitCSV');
                Route::post('', "OAuth2SummitBadgeScanApiController@add");
            });

            // badge-types
            Route::group(['prefix' => 'badge-types'], function () {
                Route::get('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitBadgeTypeApiController@getAllBySummit']);
                Route::post('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitBadgeTypeApiController@add']);
                Route::group(['prefix' => '{badge_type_id}'], function () {
                    Route::get('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitBadgeTypeApiController@get']);
                    Route::put('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitBadgeTypeApiController@update']);
                    Route::delete('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitBadgeTypeApiController@delete']);
                    Route::group(['prefix' => 'access-levels'], function () {
                        Route::group(['prefix' => '{access_level_id}'], function () {
                            Route::put('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitBadgeTypeApiController@addAccessLevelToBadgeType']);
                            Route::delete('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitBadgeTypeApiController@removeAccessLevelFromBadgeType']);
                        });
                    });
                    Route::group(['prefix' => 'features'], function () {
                        Route::group(['prefix' => '{feature_id}'], function () {
                            Route::put('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitBadgeTypeApiController@addFeatureToBadgeType']);
                            Route::delete('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SummitBadgeTypeApiController@removeFeatureFromBadgeType']);
                        });
                    });

                });
            });

            // orders
            Route::group(['prefix' => 'orders'], function () {
                Route::get('', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitOrdersApiController@getAllBySummit']);
                Route::get('csv', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitOrdersApiController@getAllBySummitCSV']);
                Route::post('', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitOrdersApiController@add']);
                Route::group(['prefix' => '{order_id}'], function () {
                    Route::get('', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitOrdersApiController@get']);
                    Route::put('', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitOrdersApiController@update']);
                    Route::delete('', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitOrdersApiController@delete']);
                    Route::delete('refund', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitOrdersApiController@refundOrder']);

                    Route::group(['prefix' => 'tickets'], function () {
                        Route::post('', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitOrdersApiController@addTicket']);
                        Route::group(['prefix' => '{ticket_id}'], function () {
                            Route::put('', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitOrdersApiController@updateTicket']);
                            Route::get('pdf', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitOrdersApiController@getTicketPDFBySummit']);
                        });
                    });
                });
                Route::post('reserve', 'OAuth2SummitOrdersApiController@reserve');
                Route::group(['prefix' => '{hash}'], function () {
                    Route::put('checkout', 'OAuth2SummitOrdersApiController@checkout');
                    Route::group(['prefix' => 'tickets'], function () {
                        Route::get('mine', 'OAuth2SummitOrdersApiController@getMyTicketByOrderHash');
                    });
                    Route::delete('', 'OAuth2SummitOrdersApiController@cancel');
                });
            });

            // tickets
            Route::group(['prefix' => 'tickets'], function () {
                Route::get('', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitTicketApiController@getAllBySummit']);
                Route::group(['prefix' => 'csv'], function () {
                    Route::get('', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitTicketApiController@getAllBySummitCSV']);
                    Route::get('template', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitTicketApiController@getImportTicketDataTemplate']);
                    Route::post('', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitTicketApiController@importTicketData']);
                });

                Route::post('ingest', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitTicketApiController@ingestExternalTicketData']);

                Route::group(['prefix' => '{ticket_id}'], function () {
                    Route::get('', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitTicketApiController@get']);
                    // badge endpoints
                    Route::group(['prefix' => 'badge'], function () {
                        Route::post('', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitTicketApiController@createAttendeeBadge']);
                        Route::get('', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitTicketApiController@getAttendeeBadge']);
                        Route::group(['prefix' => 'current'], function () {
                            Route::delete('', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitTicketApiController@deleteAttendeeBadge']);
                            Route::put('print', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitTicketApiController@printAttendeeBadge']);
                            Route::group(['prefix' => 'features'], function () {
                                Route::group(['prefix' => '{feature_id}'], function () {
                                    Route::put('', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitTicketApiController@addAttendeeBadgeFeature']);
                                    Route::delete('', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitTicketApiController@removeAttendeeBadgeFeature']);
                                });
                            });
                            Route::group(['prefix' => 'type'], function () {
                                Route::group(['prefix' => '{type_id}'], function () {
                                    Route::put('', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitTicketApiController@updateAttendeeBadgeType']);
                                });
                            });
                        });
                    });
                    // badge endpoints
                    Route::delete('refund', [ 'middleware' => 'auth.user', 'uses' =>'OAuth2SummitTicketApiController@refundTicket']);
                });
            });

            // attendees
            Route::group(array('prefix' => 'attendees'), function () {

                Route::get('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitAttendeesApiController@getAttendeesBySummit']);
                Route::get('csv', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitAttendeesApiController@getAttendeesBySummitCSV']);
                Route::get('me', 'OAuth2SummitAttendeesApiController@getOwnAttendee');
                Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitAttendeesApiController@addAttendee']);

                Route::group(array('prefix' => '{attendee_id}'), function () {
                    Route::get('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitAttendeesApiController@getAttendee']);
                    Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitAttendeesApiController@updateAttendee']);
                    Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitAttendeesApiController@deleteAttendee']);
                    // attendee schedule
                    Route::group(array('prefix' => 'schedule'), function ()
                    {
                        Route::get('', 'OAuth2SummitAttendeesApiController@getAttendeeSchedule')->where('attendee_id', 'me');

                        Route::group(array('prefix' => '{event_id}'), function (){
                            Route::post('', 'OAuth2SummitAttendeesApiController@addEventToAttendeeSchedule')->where('attendee_id', 'me|[0-9]+');
                            Route::delete('', 'OAuth2SummitAttendeesApiController@removeEventFromAttendeeSchedule')->where('attendee_id', 'me|[0-9]+');
                            Route::delete('/rsvp', 'OAuth2SummitAttendeesApiController@deleteEventRSVP')->where('attendee_id', 'me|[0-9]+');
                            Route::put('/check-in', 'OAuth2SummitAttendeesApiController@checkingAttendeeOnEvent')->where('attendee_id', 'me|[0-9]+');
                        });
                    });

                    // attendee tickets
                    Route::group(array('prefix' => 'tickets'), function ()
                    {
                        Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitAttendeesApiController@addAttendeeTicket']);
                        Route::group(array('prefix' => '{ticket_id}'), function (){
                            Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitAttendeesApiController@deleteAttendeeTicket']);
                            Route::group(array('prefix' => 'reassign'), function (){
                                Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitAttendeesApiController@reassignAttendeeTicket']);
                                Route::put('{other_member_id}', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitAttendeesApiController@reassignAttendeeTicketByMember']);
                            });
                        });
                    });
                });
            });

            // badges
            Route::group(['prefix' => 'badges'], function () {
                Route::get('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitBadgesApiController@getAllBySummit']);
                Route::get('csv', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitBadgesApiController@getAllBySummitCSV']);
            });

            // external orders @todo to deprecate
            Route::group(['prefix' => 'external-orders'], function () {
                Route::get('{external_order_id}', 'OAuth2SummitApiController@getExternalOrder');
                Route::post('{external_order_id}/external-attendees/{external_attendee_id}/confirm', 'OAuth2SummitApiController@confirmExternalOrderAttendee');
            });

            // members
            Route::group(array('prefix' => 'members'), function () {
                Route::get("",  [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitMembersApiController@getAllBySummit']);
                Route::get("csv",  [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitMembersApiController@getAllBySummitCSV']);
                Route::group(array('prefix' => '{member_id}'), function () {
                    Route::get('', 'OAuth2SummitMembersApiController@getMyMember')->where('member_id', 'me');
                    // favorites
                    Route::group(array('prefix' => 'favorites'), function ()
                    {
                        Route::get('', 'OAuth2SummitMembersApiController@getMemberFavoritesSummitEvents')->where('member_id', 'me');

                        Route::group(array('prefix' => '{event_id}'), function (){
                            Route::post('', 'OAuth2SummitMembersApiController@addEventToMemberFavorites')->where('member_id', 'me');
                            Route::delete('', 'OAuth2SummitMembersApiController@removeEventFromMemberFavorites')->where('member_id', 'me');
                        });
                    });

                    // schedule
                    Route::group(array('prefix' => 'schedule'), function ()
                    {
                        Route::get('', 'OAuth2SummitMembersApiController@getMemberScheduleSummitEvents')->where('member_id', 'me');

                        Route::group(array('prefix' => '{event_id}'), function (){
                            Route::delete('/rsvp', 'OAuth2SummitMembersApiController@deleteEventRSVP')->where('member_id', 'me');
                            Route::post('', 'OAuth2SummitMembersApiController@addEventToMemberSchedule')->where('member_id', 'me');
                            Route::delete('', 'OAuth2SummitMembersApiController@removeEventFromMemberSchedule')->where('member_id', 'me');
                        });
                    });
                });

            });

            // tracks
            Route::group(['prefix' => 'tracks'], function () {
                Route::get('', 'OAuth2SummitTracksApiController@getAllBySummit');
                Route::get('csv', 'OAuth2SummitTracksApiController@getAllBySummitCSV');
                Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitTracksApiController@addTrackBySummit']);
                Route::post('copy/{to_summit_id}', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitTracksApiController@copyTracksToSummit']);
                Route::group(['prefix' => '{track_id}'], function () {
                    Route::get('', 'OAuth2SummitTracksApiController@getTrackBySummit');
                    Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitTracksApiController@updateTrackBySummit']);
                    Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitTracksApiController@deleteTrackBySummit']);
                    Route::group(['prefix' => 'allowed-tags'], function () {
                        Route::get('', 'OAuth2SummitTracksApiController@getTrackAllowedTagsBySummit');
                    });

                    Route::group(['prefix' => 'extra-questions'], function () {
                        Route::get('', 'OAuth2SummitTracksApiController@getTrackExtraQuestionsBySummit');
                        Route::group(['prefix' => '{question_id}'], function () {

                            Route::put('', [
                                'middleware' => 'auth.user',
                                'uses' => 'OAuth2SummitTracksApiController@addTrackExtraQuestion']
                            );

                            Route::delete('', [
                                'middleware' => 'auth.user',
                                'uses' => 'OAuth2SummitTracksApiController@removeTrackExtraQuestion'
                            ]);
                        });
                    });
                });
            });

            // track groups
            Route::group(['prefix' => 'track-groups'], function () {
                Route::get('', 'OAuth2PresentationCategoryGroupController@getAllBySummit');
                Route::get('csv', 'OAuth2PresentationCategoryGroupController@getAllBySummitCSV');
                Route::get('metadata', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2PresentationCategoryGroupController@getMetadata']);
                Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2PresentationCategoryGroupController@addTrackGroupBySummit']);

                Route::group(['prefix' => '{track_group_id}'], function () {
                    Route::get('', 'OAuth2PresentationCategoryGroupController@getTrackGroupBySummit');
                    Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2PresentationCategoryGroupController@updateTrackGroupBySummit']);
                    Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2PresentationCategoryGroupController@deleteTrackGroupBySummit']);

                    Route::group(['prefix' => 'tracks'], function () {

                        Route::group(['prefix' => '{track_id}'], function () {
                            Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2PresentationCategoryGroupController@associateTrack2TrackGroup']);
                            Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2PresentationCategoryGroupController@disassociateTrack2TrackGroup']);
                        });
                    });
                    Route::group(['prefix' => 'allowed-groups'], function () {

                        Route::group(['prefix' => '{group_id}'], function () {
                            Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2PresentationCategoryGroupController@associateAllowedGroup2TrackGroup']);
                            Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2PresentationCategoryGroupController@disassociateAllowedGroup2TrackGroup']);
                        });
                    });
                });
            });

            // promo codes
            Route::group(['prefix' => 'promo-codes'], function () {
                Route::get('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitPromoCodesApiController@getAllBySummit']);
                Route::get('csv', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitPromoCodesApiController@getAllBySummitCSV']);
                Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitPromoCodesApiController@addPromoCodeBySummit']);
                Route::get('metadata', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitPromoCodesApiController@getMetadata']);
                Route::group(['prefix' => '{promo_code_id}'], function () {
                    Route::get('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitPromoCodesApiController@getPromoCodeBySummit']);
                    Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitPromoCodesApiController@updatePromoCodeBySummit']);
                    Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitPromoCodesApiController@deletePromoCodeBySummit']);
                    Route::post('mail', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitPromoCodesApiController@sendPromoCodeMail']);
                    Route::group(['prefix' => 'badge-features'], function () {
                        Route::group(['prefix' => '{badge_feature_id}'], function () {
                            Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitPromoCodesApiController@addBadgeFeatureToPromoCode']);
                            Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitPromoCodesApiController@removeBadgeFeatureFromPromoCode']);
                        });
                    });

                    Route::group(['prefix' => 'ticket-types'], function () {
                        Route::group(['prefix' => '{ticket_type_id}'], function () {
                            Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitPromoCodesApiController@addTicketTypeToPromoCode']);
                            Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitPromoCodesApiController@removeTicketTypeFromPromoCode']);
                        });
                    });
                });
            });

            // track tag groups
            Route::group(['prefix' => 'track-tag-groups'], function(){

                Route::get('', ['uses' => 'OAuth2SummitTrackTagGroupsApiController@getTrackTagGroupsBySummit']);

                Route::post('', [ 'middleware' => 'auth.user',
                    'uses' => 'OAuth2SummitTrackTagGroupsApiController@addTrackTagGroup']);

                Route::post('seed-defaults', [ 'middleware' => 'auth.user',
                    'uses' => 'OAuth2SummitTrackTagGroupsApiController@seedDefaultTrackTagGroups']);

                Route::group(['prefix' => '{track_tag_group_id}'], function(){
                    Route::get('', [ 'middleware' => 'auth.user',
                        'uses' => 'OAuth2SummitTrackTagGroupsApiController@getTrackTagGroup']);
                    Route::put('', [ 'middleware' => 'auth.user',
                        'uses' => 'OAuth2SummitTrackTagGroupsApiController@updateTrackTagGroup']);
                    Route::delete('', [ 'middleware' => 'auth.user',
                        'uses' => 'OAuth2SummitTrackTagGroupsApiController@deleteTrackTagGroup']);

                    Route::group(['prefix' => 'allowed-tags'], function(){

                        Route::group(['prefix' => 'all'], function(){
                            Route::post('copy/tracks/{track_id}',
                                [ 'middleware' => 'auth.user',
                                    'uses' => 'OAuth2SummitTrackTagGroupsApiController@seedTagTrackGroupOnTrack']);
                        });
                    });

                });

                Route::group(['prefix' => 'all'], function(){
                    Route::group(['prefix' => 'allowed-tags'], function(){

                        Route::get('', [ 'middleware' => 'auth.user',
                            'uses' => 'OAuth2SummitTrackTagGroupsApiController@getAllowedTags']);


                        Route::group(['prefix' => '{tag_id}'], function(){
                            Route::post('seed-on-tracks',
                                [ 'middleware' => 'auth.user',
                                    'uses' => 'OAuth2SummitTrackTagGroupsApiController@seedTagOnAllTracks']);
                        });
                    });
                });
            });

        });
    });

    // sponsorship-types
    Route::group(['prefix' => 'sponsorship-types'], function () {
        Route::get('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SponsorshipTypeApiController@getAll']);
        Route::post('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SponsorshipTypeApiController@add']);
        Route::group(['prefix' => '{id}'], function () {
            Route::get('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SponsorshipTypeApiController@get']);
            Route::put('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SponsorshipTypeApiController@update']);
            Route::delete('', ['middleware' => 'auth.user', 'uses' => 'OAuth2SponsorshipTypeApiController@delete']);
        });
    });

    // speakers
    Route::group(['prefix' => 'speakers'], function () {

        Route::get('', 'OAuth2SummitSpeakersApiController@getAll');
        Route::post('', 'OAuth2SummitSpeakersApiController@addSpeaker');
        Route::put('merge/{speaker_from_id}/{speaker_to_id}', 'OAuth2SummitSpeakersApiController@merge');

        Route::group(['prefix' => 'active-involvements'], function(){
            Route::get('', 'OAuth2SpeakerActiveInvolvementApiController@getAll');
        });

        Route::group(['prefix' => 'organizational-roles'], function(){
            Route::get('', 'OAuth2SpeakerOrganizationalRoleApiController@getAll');
        });

        Route::group(['prefix' => 'me'], function(){
             Route::get('', 'OAuth2SummitSpeakersApiController@getMySpeaker');
             Route::post('', 'OAuth2SummitSpeakersApiController@createMySpeaker');
             Route::put('', 'OAuth2SummitSpeakersApiController@updateMySpeaker');
             Route::post('/photo', 'OAuth2SummitSpeakersApiController@addMySpeakerPhoto');

             Route::group(['prefix' => 'presentations'], function(){

                 Route::group(['prefix' => '{presentation_id}'], function(){

                     Route::group(['prefix' => 'speakers'], function(){
                         Route::put('{speaker_id}', 'OAuth2SummitSpeakersApiController@addSpeakerToMyPresentation');
                         Route::delete('{speaker_id}', 'OAuth2SummitSpeakersApiController@removeSpeakerFromMyPresentation');
                     });
                     Route::group(['prefix' => 'moderators'], function(){
                         Route::put('{speaker_id}', 'OAuth2SummitSpeakersApiController@addModeratorToMyPresentation');
                         Route::delete('{speaker_id}', 'OAuth2SummitSpeakersApiController@removeModeratorFromMyPresentation');
                     });
                 });
                 Route::group(['prefix' => '{role}'], function(){
                     Route::group(['prefix' => 'selection-plans'], function(){
                         Route::group(['prefix' => '{selection_plan_id}'], function(){
                            Route::get("", "OAuth2SummitSpeakersApiController@getMySpeakerPresentationsByRoleAndBySelectionPlan")
                                ->where('role', 'creator|speaker|moderator');
                         });
                     });

                     Route::group(['prefix' => 'summits'], function(){
                         Route::group(['prefix' => '{summit_id}'], function(){
                             Route::get("", "OAuth2SummitSpeakersApiController@getMySpeakerPresentationsByRoleAndBySummit")
                                 ->where('role', 'creator|speaker|moderator');
                         });
                     });
                 });
             });
        });

        Route::group(['prefix' => '{speaker_id}'], function () {
            Route::put('/edit-permission', 'OAuth2SummitSpeakersApiController@requestSpeakerEditPermission')->where('speaker_id', '[0-9]+');
            Route::get('/edit-permission', 'OAuth2SummitSpeakersApiController@getSpeakerEditPermission')->where('speaker_id', '[0-9]+');
            Route::put('','OAuth2SummitSpeakersApiController@updateSpeaker')->where('speaker_id', 'me|[0-9]+');
            Route::delete('',[ 'middleware' => 'auth.user', 'uses' => 'OAuth2SummitSpeakersApiController@deleteSpeaker'])->where('speaker_id', 'me|[0-9]+');
            Route::get('', 'OAuth2SummitSpeakersApiController@getSpeaker');
            Route::post('/photo', 'OAuth2SummitSpeakersApiController@addSpeakerPhoto');
        });
    });

    // track question templates
    Route::group(['prefix' => 'track-question-templates'], function () {

        Route::get('', [
            'middleware' => 'auth.user',
            'uses' => 'OAuth2TrackQuestionsTemplateApiController@getTrackQuestionTemplates']);
        Route::get('metadata', [
            'middleware' => 'auth.user',
            'uses' => 'OAuth2TrackQuestionsTemplateApiController@getTrackQuestionTemplateMetadata'
        ]);

        Route::post('', [
            'middleware' => 'auth.user',
            'uses' => 'OAuth2TrackQuestionsTemplateApiController@addTrackQuestionTemplate']);

        Route::group(['prefix' => '{track_question_template_id}'], function () {

            Route::get('', [ 'middleware' => 'auth.user',
                'uses' => 'OAuth2TrackQuestionsTemplateApiController@getTrackQuestionTemplate']);

            Route::put('', [
                'middleware' => 'auth.user',
                'uses' => 'OAuth2TrackQuestionsTemplateApiController@updateTrackQuestionTemplate']);

            Route::delete('', [
                'middleware' => 'auth.user',
                'uses' => 'OAuth2TrackQuestionsTemplateApiController@deleteTrackQuestionTemplate']);

            // multi values questions
            Route::group(['prefix' => 'values'], function () {
                Route::post('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2TrackQuestionsTemplateApiController@addTrackQuestionTemplateValue']);
                Route::group(['prefix' => '{track_question_template_value_id}'], function () {
                    Route::get('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2TrackQuestionsTemplateApiController@getTrackQuestionTemplateValue']);
                    Route::put('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2TrackQuestionsTemplateApiController@updateTrackQuestionTemplateValue']);
                    Route::delete('', [ 'middleware' => 'auth.user', 'uses' => 'OAuth2TrackQuestionsTemplateApiController@deleteTrackQuestionTemplateValue']);
                });
            });
        });
    });
});

//OAuth2 Protected API V2
Route::group([
    'namespace'  => 'App\Http\Controllers',
    'prefix'     => 'api/v2',
    'before'     => [],
    'after'      => [],
    'middleware' => ['ssl', 'oauth2.protected', 'rate.limit','etags']
], function () {

    // summits
    Route::group(['prefix' => 'summits'], function () {

        Route::group(['prefix' => '{id}'], function () {

            Route::get('', ['uses' => 'OAuth2SummitApiController@getSummit'])->where('id', 'current|[0-9]+');
            // events
            Route::group(['prefix' => 'events'], function () {

                Route::group(['prefix' => '{event_id}'], function () {
                   Route::post('/feedback', 'OAuth2SummitEventsApiController@addEventFeedbackByMember');
                   Route::put('/feedback', 'OAuth2SummitEventsApiController@updateEventFeedbackByMember');
                });
            });
        });
    });
});