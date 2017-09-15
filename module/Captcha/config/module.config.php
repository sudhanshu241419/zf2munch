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
            'captcha' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/captcha',
                    'defaults' => array(
                        'controller' => 'Captcha\Controller\Captcha'
                    )
                )
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Captcha\Controller\Captcha' => 'Captcha\Controller\CaptchaController'
        )
    )
);
