<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
return array(
    'router' => array(
        'routes' => array(
            'home-location' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/home/location',
                    'defaults' => array(
                        'controller' => 'Home\Controller\Location'
                    )
                )
            ),
            'home-social' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/home/social',
                    'defaults' => array(
                        'controller' => 'Home\Controller\Social'
                    )
                )
            ),
            'home-current-timeslots-city' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/currenttime',
                    'defaults' => array(
                        'controller' => 'Home\Controller\CurrentTimeslots'
                    )
                )
            ),
            'home-current-timeslots-restaurants' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/currenttime[/:id]',
                    'defaults' => array(
                        'controller' => 'Home\Controller\CurrentTimeslots'
                    )
                )
            ),
            'home-mood-autocomplete' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/home/mood/autocomplete',
                    'defaults' => array(
                        'controller' => 'Home\Controller\MoodAutoComplete'
                    )
                )
            ),
            'timeslots-city' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/timeslots',
                    'defaults' => array(
                        'controller' => 'Home\Controller\Timeslots'
                    )
                )
            ),
            'timeslots-restaurants' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/timeslots[/:id]',
                    'defaults' => array(
                        'controller' => 'Home\Controller\Timeslots'
                    )
                )
            ),
            'order-timeslot-restaurants' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/currenttimeslot[/:id]',
                    'defaults' => array(
                        'controller' => 'Home\Controller\OrderTimeslot'
                    )
                )
            ),
            'home-social-follow' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/home/socialfollowers',
                    'defaults' => array(
                        'controller' => 'Home\Controller\SocialFollowCount'
                    )
                )
            ),
            'home-cities' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/home/city',
                    'defaults' => array(
                        'controller' => 'Home\Controller\City'
                    )
                )
            ),
            'home-populartag' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/home/populartags',
                    'defaults' => array(
                        'controller' => 'Home\Controller\PopularSearchTag'
                    )
                )
            ),
            'home-location_api' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/home/location',
                    'defaults' => array(
                        'controller' => 'Home\Controller\Location'
                    )
                )
            ),
            'home-campaigns_api' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/home/campaigns',
                    'defaults' => array(
                        'controller' => 'Home\Controller\Campaigns'
                    )
                )
            ),
            'home-banner_api' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/home/banner',
                    'defaults' => array(
                        'controller' => 'Home\Controller\Banners'
                    )
                )
            ),
            'home-fource_update_api' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/home/updateapp',
                    'defaults' => array(
                        'controller' => 'Home\Controller\FourceUpdateApp'
                    )
                )
            ),
            'salesmanago-hosturl_user_custome_detail' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/home/salesmango',
                    'defaults' => array(
                        'controller' => 'Home\Controller\Salesmango'
                    )
                )
            ),
            'api-app-info' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/home/appconfig',
                    'defaults' => array(
                        'controller' => 'Home\Controller\AppConfig'
                    )
                )
            ), 
            'ma-promocode' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/home/promocode',
                    'defaults' => array(
                        'controller' => 'Home\Controller\MaPromocode'
                    )
                )
            ),
            'ios-and-promocode' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/home/promocode',
                    'defaults' => array(
                        'controller' => 'Home\Controller\MobPromocode'
                    )
                )
            ),
            'ma-career' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/home/career',
                    'defaults' => array(
                        'controller' => 'Home\Controller\MaCarrier'
                    )
                )
            ),
            'ma-contact' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/home/contact',
                    'defaults' => array(
                        'controller' => 'Home\Controller\MaCarrier'
                    )
                )
            ),
            
            
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Home\Controller\Location' => 'Home\Controller\LocationController',
            'Home\Controller\Social' => 'Home\Controller\SocialController',
            'Home\Controller\CurrentTimeslots' => 'Home\Controller\CurrentTimeslotsController',
            'Home\Controller\MoodAutoComplete' => 'Home\Controller\MoodAutoCompleteController',
            'Home\Controller\Timeslots' => 'Home\Controller\TimeslotsController',
            'Home\Controller\OrderTimeslot' => 'Home\Controller\OrderTimeslotController',
            'Home\Controller\SocialFollowCount' => 'Home\Controller\SocialFollowCountController',
            'Home\Controller\City'=>'Home\Controller\CityController',
            'Home\Controller\PopularSearchTag'=>'Home\Controller\PopularSearchTagController',
            'Home\Controller\Campaigns'=>'Home\Controller\CampaignsController',
            'Home\Controller\Banners'=>'Home\Controller\BannersController',
            'Home\Controller\FourceUpdateApp'=>'Home\Controller\FourceUpdateAppController',
            'Home\Controller\Salesmango'=>'Home\Controller\SalesmangoController',
            'Home\Controller\AppConfig'=>'Home\Controller\AppConfigController',
            'Home\Controller\MaPromocode'=>'Home\Controller\MaPromocodeController',
            'Home\Controller\MaCarrier'=>'Home\Controller\MaCarrierController',
            'Home\Controller\MobPromocode'=>'Home\Controller\MobPromocodeController'
            
        )
    )
);
