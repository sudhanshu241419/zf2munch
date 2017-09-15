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
            'aria-restaurant-details' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/aria/details[/:id]',
                    'defaults' => array(
                        'controller' => 'Ariahk\Controller\AriaRestaurantGeneralDetails'
                    )
                )
            ),
            'aria-menu-addons' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/aria/menu-addons[/:id]',
                    'defaults' => array(
                        'controller' => 'Ariahk\Controller\AriaMenuAddons'
                    )
                )
            ),
            'aria-restaurant-timeslot' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/aria/timeslot[/:id]',
                    'defaults' => array(
                        'controller' => 'Ariahk\Controller\AriaRestaurantTimeSlot'
                    )
                )
            ),
            'aria-restaurant-reservartion' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/aria/reservation[/:id]',
                    'defaults' => array(
                        'controller' => 'Ariahk\Controller\AriaReservation'
                    )
                )
            ),
            'aria-restaurant-order' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/aria/place-order[/:id]',
                    'defaults' => array(
                        'controller' => 'Ariahk\Controller\AriaOrder'
                    )
                )
            ),
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Ariahk\Controller\AriaRestaurantGeneralDetails' => 'Ariahk\Controller\AriaRestaurantGeneralDetailsController',
            'Ariahk\Controller\AriaRestaurantTimeSlot'       => 'Ariahk\Controller\AriaRestaurantTimeSlotController',
            'Ariahk\Controller\AriaMenuAddons'               => 'Ariahk\Controller\AriaMenuAddonsController',
            'Ariahk\Controller\AriaReservation'              => 'Ariahk\Controller\AriaReservationController',
            'Ariahk\Controller\AriaOrder'                    => 'Ariahk\Controller\AriaOrderController',
        )
    ),
    'view_manager' => array(
        'template_map' => array(
            'email-layout/default_aria' => __DIR__ . '/../Mails/Ariahk/layouts/default_ariahk.phtml',
            'email-template/email_manager' =>__DIR__ . '/../Mails/Ariahk/templates/email_manager.phtml',
            'email-template/email_manager_order' =>__DIR__ . '/../Mails/Ariahk/templates/email_manager_order.phtml',
            'email-template/email_manager_delivery' =>__DIR__ . '/../Mails/Ariahk/templates/email_manager_order_delivery.phtml',
            
        ),
        'template_path_stack' => array(
            __DIR__ . '/../Mails'
        )
    ),
    'constants' => array(
        'order_type' => array(
            'group' => 'G',
            'individual' => 'I',
            'orderPending' => '0'
        ),
        
        'reservation_status' => array(
            'archived' => '0',
            'upcoming' => '1',
            'canceled' => '2',
            'rejected' => '3',
            'confirmed' => '4'
        ),
        'reservation_status_' => array(
            '0' => 'archived',
            '1' => 'upcoming',
            '2' => 'canceled',
            '3' => 'rejected',
            '4' => 'confirmed'
        ),
        'special_character' => array(
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ð' => 'D',
            'Ñ' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'o',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ñ' => 'n',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ý' => 'y',
            'ÿ' => 'y',
            '&Aacute;' => 'A',
            '&aacute;' => 'a',
            '&Eacute;' => 'E',
            '&eacute;' => 'e',
            '&Iacute;' => 'I',
            '&iacute;' => 'i',
            '&Oacute;' => 'O',
            '&oacute;' => 'o',
            '&Ntilde;' => 'N',
            '&ntilde;' => 'n',
            '&Uacute;' => 'U',
            '&uacute;' => 'u',
            '&Uuml;' => 'U',
            '&uuml;' => 'u',
            '&#268' => 'C',
            '&#352;' => 'S',
            '&#381;' => 'Z',
            '&#269;' => 'c',
            '&#353;' => 's',
            '&#382;' => 'z'
        ),
        'notEmailRestriction' => array(
            'forgot-password',
            'emailawarding',
            'modify-reservation',
            'modify-reservation-to-friends',
            'send-cancel-reservation',
            'send-cancel-reservation-friends',
            'is_buying_food_you_in',
            'friends-reservation-Invitation')
    )
);
