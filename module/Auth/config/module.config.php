<?php

return array(
    'router' => array(
        'routes' => array(
            'api-token' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/auth/token[/:id]',
                    'defaults' => array(
                        'controller' => 'Auth\Controller\Token'
                    )
                )
            ),
            'web-api-token' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/auth/token[/:id]',
                    'defaults' => array(
                        'controller' => 'Auth\Controller\Token'
                    )
                )
            ),
            'auth-token-stash' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/auth/token/stash[/:id]',
                    'defaults' => array(
                        'controller' => 'Auth\Controller\Stash'
                    )
                )
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Auth\Controller\Token' => 'Auth\Controller\TokenController',
            'Auth\Controller\Stash' => 'Auth\Controller\StashController'
        )
    )
);
