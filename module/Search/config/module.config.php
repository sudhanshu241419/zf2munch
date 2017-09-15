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
            'restaurant-main-search' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/search',
                    'defaults' => array(
                        'controller' => 'Search\Controller\WebSearch'
                    )
                )
            ),
             'restaurant-seo-search' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/seosearch',
                    'defaults' => array(
                        'controller' => 'Search\Controller\WebSeoSearch'
                    )
                )
            ),
            'restaurant-searchweb' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/landmark',
                    'defaults' => array(
                        'controller' => 'Search\Controller\WebSearch'
                    )
                )
            ),
            'restaurant-searchbytype' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/totalrests',
                    'defaults' => array(
                        'controller' => 'Search\Controller\WebSearch'
                    )
                )
            ),
            'city-timeslots' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/timeslots',
                    'defaults' => array(
                        'controller' => 'Search\Controller\WebTimeslotsController'
                    )
                )
            ),
            'get-bookmarks' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/bookmarks',
                    'defaults' => array(
                        'controller' => 'Search\Controller\BookmarkController'
                    )
                )
            ),
            'restaurant-mobile-search' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/search',
                    'defaults' => array(
                        'controller' => 'Search\Controller\MobileSearch'
                    )
                )
            ),
            'munchado-test' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/search/test',
                    'defaults' => array(
                        'controller' => 'Search\Controller\Test'
                    )
                )
            ),
            'munchado-log' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/log',
                    'defaults' => array(
                        'controller' => 'Search\Controller\Log'
                    )
                )
            ),
            'search-etc' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/search/etc',
                    'defaults' => array(
                        'controller' => 'Search\Controller\SearchEtc'
                    )
                )
            ),
            'search-sweepstakes-api' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/search/sweepstakes',
                    'defaults' => array(
                        'controller' => 'Search\Controller\Sweepstakes'
                    )
                )
            ),
             'search-sweepstakes' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/search/sweepstakes',
                    'defaults' => array(
                        'controller' => 'Search\Controller\Sweepstakes'
                    )
                )
            ),'search-banners' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/search/banners',
                    'defaults' => array(
                        'controller' => 'Search\Controller\SearchBanners'
                    )
                )
            ),'user-deals' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/search/userdeals',
                    'defaults' => array(
                        'controller' => 'Search\Controller\UserDeals'
                    )
                )
            ),'user-deals-mob' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/search/userdeals',
                    'defaults' => array(
                        'controller' => 'Search\Controller\UserDeals'
                    )
                )
            ),
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Search\Controller\WebSearch' => 'Search\Controller\WebSearchController',
            'Search\Controller\MobileSearch' => 'Search\Controller\MobileSearchController',
            'Search\Controller\WebTimeslotsController' => 'Search\Controller\WebTimeslotsController',
            'Search\Controller\BookmarkController' => 'Search\Controller\BookmarkController',
            'Search\Controller\WebSeoSearch'=>'Search\Controller\WebSeoSearchController',
            'Search\Controller\Test'=>'Search\Controller\TestController',
            'Search\Controller\Log' => 'Search\Controller\LogController',
            'Search\Controller\SearchEtc' => 'Search\Controller\SearchEtcController',
            'Search\Controller\Sweepstakes' => 'Search\Controller\SweepstakesController',
            'Search\Controller\SearchBanners' => 'Search\Controller\SearchBannersController',
            'Search\Controller\UserDeals' => 'Search\Controller\UserDealsController',
        )
    )
);
