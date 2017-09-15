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
            'servers-register' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/servers/register',
                    'defaults' => array(
                        'controller' => 'Servers\Controller\WebRegistration'
                    )
                )
            ),
            'servers-login' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/servers/login',
                    'defaults' => array(
                        'controller' => 'Servers\Controller\WebLogin'
                    )
                )
            ),
            'servers-logout' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/servers/logout',
                    'defaults' => array(
                        'controller' => 'Servers\Controller\WebLogin'
                    )
                )
            ),
            'forgot-password' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/servers/forgot-password[/:id]',
                    'defaults' => array(
                        'controller' => 'Servers\Controller\WebLogin'
                    )
                )
            ),
            'restaurants-list' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/servers/restaurantList',
                    'defaults' => array(
                        'controller' => 'Servers\Controller\WebRegistration'
                    )
                )
            ),
            'customers-list' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/servers/customersList',
                    'defaults' => array(
                        'controller' => 'Servers\Controller\WebServerCustomers'
                    )
                )
            ),
            'leader-board' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/servers/leaderboard',
                    'defaults' => array(
                        'controller' => 'Servers\Controller\WebServerLeaderboard'
                    )
                )
            ),
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Servers\Controller\WebRegistration' => 'Servers\Controller\WebRegistrationController',
            'Servers\Controller\WebLogin' => 'Servers\Controller\WebLoginController',
            'Servers\Controller\WebServerCustomers' => 'Servers\Controller\WebServerCustomersController',
            'Servers\Controller\WebServerLeaderboard' => 'Servers\Controller\WebServerLeaderboardController',
        )
    )
);
