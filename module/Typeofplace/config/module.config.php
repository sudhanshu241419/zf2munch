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
            'typeofplace' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/typeofplace',
                    'defaults' => array(
                        'controller' => 'Typeofplace\Controller\Typeofplace'
                    )
                )
            ),
            'web-typeofplace' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/typeofplace',
                    'defaults' => array(
                        'controller' => 'Typeofplace\Controller\WebTypeofplace'
                    )
                )
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Typeofplace\Controller\Typeofplace' => 'Typeofplace\Controller\TypeofplaceController',
            'Typeofplace\Controller\WebTypeofplace' => 'Typeofplace\Controller\WebTypeofplaceController'
        )
    )
);
