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
            'restaurant-bookmark' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/bookmark/restaurantbookmark',
                    'defaults' => array(
                        'controller' => 'Bookmark\Controller\RestaurantBookmark'
                    )
                )
            ),
            'restaurant-foodbookmark' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/bookmark/foodbookmark',
                    'defaults' => array(
                        'controller' => 'Bookmark\Controller\FoodBookmark'
                    )
                )
            ),
            'person-loveit-item' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/bookmark/listofpersonloveitem[/:id]',
                    'defaults' => array(
                        'controller' => 'Bookmark\Controller\ListOfPersonLoveItem'
                    )
                )
            ),
            'restaurant-web-bookmark' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restbookmark/add',
                    'defaults' => array(
                        'controller' => 'Bookmark\Controller\WebRestaurantBookmark'
                    )
                )
            ),
            'restaurant-web-foodbookmark' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/foodbookmark/add',
                    'defaults' => array(
                        'controller' => 'Bookmark\Controller\WebFoodBookmark'
                    )
                )
            ),
            'api-feedbookmark' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/feedbookmark/add',
                    'defaults' => array(
                        'controller' => 'Bookmark\Controller\FeedBookmark'
                    )
                )
            ),
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Bookmark\Controller\RestaurantBookmark' => 'Bookmark\Controller\RestaurantBookmarkController',
            'Bookmark\Controller\FoodBookmark' => 'Bookmark\Controller\FoodBookmarkController',
            'Bookmark\Controller\ListOfPersonLoveItem' => 'Bookmark\Controller\ListOfPersonLoveItemController',
            'Bookmark\Controller\WebRestaurantBookmark' => 'Bookmark\Controller\WebRestaurantBookmarkController',
            'Bookmark\Controller\WebFoodBookmark' => 'Bookmark\Controller\WebFoodBookmarkController',
            'Bookmark\Controller\FeedBookmark'=>'Bookmark\Controller\FeedBookmarkController',
        )
    )
);
