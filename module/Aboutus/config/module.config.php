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
            'website-aboutus' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/bigado',
                    'defaults' => array(
                        'controller' => 'Aboutus\Controller\Aboutus'
                    )
                )
            ),
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Aboutus\Controller\Aboutus' => 'Aboutus\Controller\AboutusController',
        )
    )
);
