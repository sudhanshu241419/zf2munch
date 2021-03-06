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
            'website-term' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/term',
                    'defaults' => array(
                        'controller' => 'Term\Controller\Term'
                    )
                )
            ),
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Term\Controller\Term' => 'Term\Controller\TermController',
        )
    )
);
