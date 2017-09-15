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
            'cuisine' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/cuisines',
                    'defaults' => array(
                        'controller' => 'Cuisine\Controller\Cuisine'
                    )
                )
            ),
            'web-cuisine' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/cuisines',
                    'defaults' => array(
                        'controller' => 'Cuisine\Controller\WebCuisine'
                    )
                )
            ),
            'api-popular-cuisine' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/popularcuisines',
                    'defaults' => array(
                        'controller' => 'Cuisine\Controller\PopularCuisine'
                    )
                )
            )
        )
    )
    ,
    'controllers' => array(
        'invokables' => array(
            'Cuisine\Controller\Cuisine' => 'Cuisine\Controller\CuisineController',
            'Cuisine\Controller\WebCuisine' => 'Cuisine\Controller\WebCuisineController',
            'Cuisine\Controller\PopularCuisine'=>'Cuisine\Controller\PopularCuisineController'
        )
    )
);
