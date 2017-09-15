<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
return array(
     'api_standards' => array(
        // Default text 'token'
        'token_text' => 'token',
        'formatter_text' => 'response_type',
        'default_formatter' => 'json',
        'default_ttl' => 315360000
    ),
    'errors' => array(
        'show_exceptions' => array(
            'message' => true,
            'trace' => true
        )
    ),
    'di' => array(
        'instance' => array(
            'alias' => array(
                'json_processor' => 'Dashboard\Processors\Json',
                'image_processor' => 'Dashboard\Processors\Image',
                'xml_processor' => 'Dashboard\Processors\Xml',
                'phps_processor' => 'Dashboard\Processors\Phps'
            )
        )
    ),
    'city_timezones' => array(
        'SF' => 'America/Los_Angeles',
        'NY' => 'America/New_York',
        'IN' => 'Asia/Kolkata',
        'CA' => 'America/Los_Angeles'
    ),
    'router' => array(
        'routes' => array(
            'dashboard-auth' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/login[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardAuth'
                    )
                )
            ), 
            'dashboard-order' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/order[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardOrder'
                    )
                )
            ),
            'dashboard-logout' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/logout[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardLogout'
                    )
                )
            ),
            'dashboard-api-token' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/token[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\Token'
                    )
                )
            ),
            'dashboard-api-restourant' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/restaurant[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\Restaurant'
                    )
                )
            ),
            'dashboard-api-registration' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/merchant[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\MerchantRegistration'
                    )
                )
            ),
            'dashboard-api-dashboard' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/home[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\Dashboard'
                    )
                )
            ),
            'dashboard-api-forceupdate' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/updateapp[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardAppForceUpdate'
                    )
                )
            ),

            'dashboard-api-reservation' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/reservations[/:date]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardReservation'
                  )
                )
            ),
            'dashboard-api-reservation-detail' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/reservation/detail[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardReservation'
                  )
                )
            ),
             'dashboard-api-reservation-update' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/reservation/update[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardReservation'
                   )
                )
            ),
            'dashboard-api-deal' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/deals[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardDeals'
                    )
                )
            ),
            'dashboard-api-reports' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/reports[/:type][/:date][/:enddate][/:filter]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\Report'
                    )
                )
            ),
            'dashboard-api-notification' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/pubnubnotification[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardPubnubNotification'
                    )
                )
            ),
            'dashboard-forgot-password' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/forgotPass[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardAuth'
                    )
                )
            ), 
            'dashboard-restaurant-notificatio-settings' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/settings',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardSettings'
                    )
                )
            ), 
            'dashboard-update-settings' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/settings/update[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardSettings'
                    )
                )
            ), 
            'dashboard-restaurant-slots' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/restaurant/slots[/:date]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardRestaurantCalender'
                    )
                )
            ),
            'dashboard-user-guest' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/guest[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\User'
                    )
                )
            ),
            'dashboard-restaurant-delivery-hours' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/restaurant/deliveryhours',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardBusinessProfile'
                    )
                )
            ),
            'dashboard-restaurant-delivery-hours-update' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/restaurant/deliveryhours/update',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardBusinessProfile'
                    )
                )
            ),
            'dashboard-restaurant-dinein' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/restaurant/dinein/detail',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardDineIn'
                    )
                )
            ),
            'dashboard-restaurant-dinein-update' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/restaurant/dinein/update',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardDineIn'
                    )
                )
            ),
            'dashboard-api-restaurant-update' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/restaurant/update',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\Restaurant'
                    )
                )
            ),
            'dashboard-api-restaurant-reviews' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/restaurant/reviews[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardReview'
                    )
                )
            ),
            'dashboard-api-restaurant-review-update' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/restaurant/review/update[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardReview'
                    )
                )
            ),
            'dashboard-api-restaurant-dinein' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/restaurant/holdatable[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\DashboardRestaurantDinein'
                    )
                )
            ),
            'dashboard-merchant-agreement' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/dashboard/merchantagreement[/:id]',
                    'defaults' => array(
                        'controller' => 'Dashboard\Controller\MerchantAgreement'
                    )
                )
            ),
            )
            ),
    'controllers' => array(
        'invokables' => array(
            'Dashboard\Controller\DashboardAuth' => 'Dashboard\Controller\DashboardAuthController',
            'Dashboard\Controller\DashboardLogout' => 'Dashboard\Controller\DashboardLogoutController',
            'Dashboard\Controller\DashboardOrder' => 'Dashboard\Controller\DashboardOrderController',
            'Dashboard\Controller\Token'=>'Dashboard\Controller\TokenController',
            'Dashboard\Controller\Restaurant'=>'Dashboard\Controller\RestaurantController',
            'Dashboard\Controller\MerchantRegistration'=>'Dashboard\Controller\MerchantRegistrationController',
            'Dashboard\Controller\Dashboard'=>'Dashboard\Controller\DashboardController',
            'Dashboard\Controller\DashboardAppForceUpdate'=>'Dashboard\Controller\DashboardAppForceUpdateController',
            'Dashboard\Controller\DashboardDeals'=>'Dashboard\Controller\DashboardDealsController',
            'Dashboard\Controller\Report'=>'Dashboard\Controller\ReportController',
            'Dashboard\Controller\DashboardReservation' => 'Dashboard\Controller\DashboardReservationController',
            'Dashboard\Controller\DashboardPubnubNotification'=>'Dashboard\Controller\DashboardPubnubNotificationController',
            'Dashboard\Controller\DashboardSettings'=>'Dashboard\Controller\DashboardSettingsController',
            'Dashboard\Controller\DashboardRestaurantCalender'=>'Dashboard\Controller\DashboardRestaurantCalenderController',
            'Dashboard\Controller\DashboardBusinessProfile'=>'Dashboard\Controller\DashboardBusinessProfileController',
            'Dashboard\Controller\DashboardDineIn'=>'Dashboard\Controller\DashboardDineInController',
            'Dashboard\Controller\DashboardReview'=>'Dashboard\Controller\DashboardReviewController',
            'Dashboard\Controller\User'=>'Dashboard\Controller\UserController',
            'Dashboard\Controller\DashboardRestaurantDinein'=>'Dashboard\Controller\DashboardRestaurantDineinController',
            'Dashboard\Controller\MerchantAgreement'=>'Dashboard\Controller\MerchantAgreementController',
            )

    ),
    'view_manager' => array(
        'template_map' => array(
            'email-layout/default_new' => __DIR__ . '/../Mails/Dashboard/layouts/default_new.phtml',
            'email-layout/default_aria' => __DIR__ . '/../Mails/Dashboard/layouts/default_aria.phtml',
            'email-layout/default_b2b' => __DIR__ . '/../Mails/Dashboard/layouts/default_b2b.phtml',
            'email-template/10_Order-Up-Takeout' => __DIR__ . '/../Mails/Dashboard/templates/10_Order-Up-Takeout/10_Order-Up-Takeout.phtml',
            'email-template/11_Uh-Oh-No-Food-For-You' => __DIR__ . '/../Mails/Dashboard/templates/11_Uh-Oh-No-Food-For-You/11_Uh-Oh-No-Food-For-You.phtml',
            'email-template/Aria_Cancel_Order'=>__DIR__ . '/../Mails/Dashboard/templates/Aria_Cancel_Order/Aria_Cancel_Order.phtml',
            'email-template/Aria_Order_Up'=>__DIR__ . '/../Mails/Dashboard/templates/Aria_Cancel_Order/Aria_Order_Up.phtml',
            'email-template/Aria_Order_Up_takeout'=>__DIR__ . '/../Mails/Dashboard/templates/Aria_Order_Up_takeout/Aria_Order_Up_takeout.phtml',
            'email-template/10_Order-Up'=>__DIR__ . '/../Mails/Dashboard/templates/10_Order-Up/10_Order-Up.phtml',
            'email-template/05_Your-Reservation-has-been-Reserved'=>__DIR__ . '/../Mails/Dashboard/templates/05_Your-Reservation-has-been-Reserved/05_Your-Reservation-has-been-Reserved.phtml',
            'email-template/07_About-That-Meal-You-Reserved'=>__DIR__ . '/../Mails/Dashboard/templates/07_About-That-Meal-You-Reserved/07_About-That-Meal-You-Reserved.phtml',
            'email-template/23_Pre-Order-With-Reservation-Cancellation'=>__DIR__ . '/../Mails/Dashboard/templates/23_Pre-Order-With-Reservation-Cancellation/23_Pre-Order-With-Reservation-Cancellation.phtml',            
            'email-template/Order-Ready'=>__DIR__ . '/../Mails/Dashboard/templates/Order-ready/Order-Ready.phtml',

           'email-template/05_Your-Reservation-has-been-Reserved'=>__DIR__ . '/../Mails/Dashboard/templates/05_Your-Reservation-has-been-Reserved/05_Your-Reservation-has-been-Reserved.phtml',
            'email-template/07_About-That-Meal-You-Reserved'=>__DIR__ . '/../Mails/Dashboard/templates/07_About-That-Meal-You-Reserved/07_About-That-Meal-You-Reserved.phtml',
            'email-template/23_Pre-Order-With-Reservation-Cancellation'=>__DIR__ . '/../Mails/Dashboard/templates/23_Pre-Order-With-Reservation-Cancellation/23_Pre-Order-With-Reservation-Cancellation.phtml',
            'email-template/04_HungryBuzz-Password-Recovery-Squad-Says-Panic!'=>__DIR__ . '/../Mails/Dashboard/templates/04_HungryBuzz-Password-Recovery-Squad-Says-Panic!/04_HungryBuzz-Password-Recovery-Squad-Says-Panic!.phtml',
            'email-template/20_The_Voice_of_the_Little_Guy_Was_Heard_by_the_Big_Guy'=>__DIR__ . '/../Mails/Dashboard/templates/20_The_Voice_of_the_Little_Guy_Was_Heard_by_the_Big_Guy/20_The_Voice_of_the_Little_Guy_Was_Heard_by_the_Big_Guy.phtml',
            'email-template/About_Your_Table_Request_at'=>__DIR__ . '/../Mails/Dashboard/templates/About_Your_Table_Request_at/About_Your_Table_Request_at.phtml',
            'email-template/B2B_registration_CRM_freelisting'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_freelisting/B2B_registration_CRM_freelisting.phtml',
            'email-template/B2B_registration_CRM_loyalty'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_loyalty/B2B_registration_CRM_loyalty.phtml',
            'email-template/B2B_registration_CRM_eCommerce_99'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_eCommerce_99/B2B_registration_CRM_eCommerce_99.phtml',
            'email-template/B2B_registration_CRM_eCommerce_99_pdf'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_eCommerce_99/B2B_registration_CRM_eCommerce_99_pdf.phtml',
            'email-template/B2B_registration_CRM_eCommerce_199'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_eCommerce_199/B2B_registration_CRM_eCommerce_199.phtml',
            'email-template/B2B_registration_CRM_Marketing'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_Marketing/B2B_registration_CRM_Marketing.phtml',
            'email-template/B2B_registration_CRM_Social_Media'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_Social_Media/B2B_registration_CRM_Social_Media.phtml',
            'email-template/B2B_registration_CRM_eCommerce_99_and_Marketing'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_eCommerce_99_and_Marketing/B2B_registration_CRM_eCommerce_99_and_Marketing.phtml',
            'email-template/B2B_registration_CRM_eCommerce_99_and_Social_Media'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_eCommerce_99_and_Social_Media/B2B_registration_CRM_eCommerce_99_and_Social_Media.phtml',
            'email-template/B2B_registration_CRM_eCommerce_199_and_Marketing'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_eCommerce_199_and_Marketing/B2B_registration_CRM_eCommerce_199_and_Marketing.phtml',
            'email-template/B2B_registration_CRM_eCommerce_199_and_Social_Media'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_eCommerce_199_and_Social_Media/B2B_registration_CRM_eCommerce_199_and_Social_Media.phtml',
            'email-template/B2B_registration_CRM_Marketing_and_Social_Media'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_Marketing_and_Social_Media/B2B_registration_CRM_Marketing_and_Social_Media.phtml',
            'email-template/Reservation-Confirmation'=>__DIR__ . '/../Mails/Dashboard/templates/Reservation-Confirmation/Reservation-Confirmation.phtml',
            'email-template/Reservation-Modification'=>__DIR__ . '/../Mails/Dashboard/templates/Reservation-Modification/Reservation-Modification.phtml',
            'email-template/Reservation-Cancellation'=>__DIR__ . '/../Mails/Dashboard/templates/Reservation-Cancellation/Reservation-Cancellation.phtml',
            'email-template/B2B_registration_CRM_freelisting_pdf'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_freelisting/B2B_registration_CRM_freelisting_pdf.phtml',
            'email-template/B2B_registration_CRM_loyalty_pdf'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_loyalty/B2B_registration_CRM_loyalty_pdf.phtml',
            'email-template/B2B_registration_CRM_Marketing_pdf'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_Marketing/B2B_registration_CRM_Marketing_pdf.phtml',
            'email-template/B2B_registration_CRM_eCommerce_199_pdf'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_eCommerce_199/B2B_registration_CRM_eCommerce_199_pdf.phtml',
            'email-template/B2B_registration_CRM_Social_Media_pdf'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_Social_Media/B2B_registration_CRM_Social_Media_pdf.phtml',
            'email-template/B2B_registration_CRM_eCommerce_99_and_Marketing_pdf'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_eCommerce_99_and_Marketing/B2B_registration_CRM_eCommerce_99_and_Marketing_pdf.phtml',
            'email-template/B2B_registration_CRM_eCommerce_99_and_Social_Media_pdf'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_eCommerce_99_and_Social_Media/B2B_registration_CRM_eCommerce_99_and_Social_Media_pdf.phtml',
            'email-template/B2B_registration_CRM_eCommerce_199_and_Marketing_pdf'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_eCommerce_199_and_Marketing/B2B_registration_CRM_eCommerce_199_and_Marketing_pdf.phtml',
            'email-template/B2B_registration_CRM_eCommerce_199_and_Social_Media_pdf'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_eCommerce_199_and_Social_Media/B2B_registration_CRM_eCommerce_199_and_Social_Media_pdf.phtml',
            'email-template/B2B_registration_CRM_Marketing_and_Social_Media_pdf'=>__DIR__ . '/../Mails/Dashboard/templates/B2B_registration_CRM_Marketing_and_Social_Media/B2B_registration_CRM_Marketing_and_Social_Media_pdf.phtml',
            'email-template/pre_order_confirm'=>__DIR__ . '/../Mails/Dashboard/templates/pre_order_confirm/pre_order_confirm.phtml',
            ),  
        'template_path_stack' => array(
            __DIR__ . '/../Mails'
        )
    ),   
    
);
