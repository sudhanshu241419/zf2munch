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
            'user-reservation' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/reservation[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\Reservation'
                    )
                )
            ),
            'mymunchado-reservation' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/reservation',
                    'defaults' => array(
                        'controller' => 'User\Controller\MyReservation'
                    )
                )
            ),
            'web-user-login' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/login[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebLogin'
                    )
                )
            ),
            'user-login' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/login[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\Login'
                    )
                )
            ),
            'mob-forgot-password' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/forgot-password[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\ForgotPassword'
                    )
                )
            ),
            'user-points' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/userpoints',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserPoints'
                    )
                )
            ),
            'point-source' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/pointsource',
                    'defaults' => array(
                        'controller' => 'User\Controller\PointSource'
                    )
                )
            ),
            'user-notification' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/notification',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserNotification'
                    )
                )
            ),
            'user-current-notification' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/current-notification[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserCurrentNotification'
                    )
                )
            ),
            'status-change-notification' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/change-status[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserNotification'
                    )
                )
            ),
            'read-notification-list' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/read-notification-list',
                    'defaults' => array(
                        'controller' => 'User\Controller\ReadNotificationList'
                    )
                )
            ),
            'user-details' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/details[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserDetails'
                    )
                )
            ),
            'user-reviews-list' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/reviews-list',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserReviewsList'
                    )
                )
            ),
            'menu-bookmarks' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/menu-bookmarks',
                    'defaults' => array(
                        'controller' => 'User\Controller\MenuBookmarks'
                    )
                )
            ),
            'restaurant-bookmarks' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/restaurant-bookmarks',
                    'defaults' => array(
                        'controller' => 'User\Controller\RestaurantBookmarks'
                    )
                )
            ),
            'user-reviews-detail' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/reviews-detail[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserReview'
                    )
                )
            ),
            'user-profile-update' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/user-profile-update[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserProfileUpdate'
                    )
                )
            ),
            'user-notification-settings' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/email-notification[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserNotificationSettings'
                    )
                )
            ),
            'user-reservation-again' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/reservationagain[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\ReservationAgain'
                    )
                )
            ),
            'user-invitation-friendlist' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/invitedfriendlist[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\InvitedFriendList'
                    )
                )
            ),
            'user-deals-coupons' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/deals-coupons[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\DealsCoupons'
                    )
                )
            ),
            'user-review' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/review[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\Review'
                    )
                )
            ),
            'user-usermenureview' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/usermenureview[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\Usermenureview'
                    )
                )
            ),
            'send-invitation' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/sendinvitation[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\SendInvitation'
                    )
                )
            ),
            'user-oreder-place' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/orderplace[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\OrderPlace'
                    )
                )
            ),
            'user-recent-tiredandplace' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/recents',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserRecents'
                    )
                )
            ),
            'user-report' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/report',
                    'defaults' => array(
                        'controller' => 'User\Controller\ReportProblem'
                    )
                )
            ),
            'user-address' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/address[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserAddress'
                    )
                )
            ),
            'user-card' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/card[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\CreditCard'
                    )
                )
            ),
            'user-restaurant-photo' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/addphoto[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserRestaurantImage'
                    )
                )
            ),
            'user-location' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/location',
                    'defaults' => array(
                        'controller' => 'User\Controller\Location'
                    )
                )
            ),
            'web-user-location' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/location',
                    'defaults' => array(
                        'controller' => 'User\Controller\Location'
                    )
                )
            ),
            'web-user-forgot-password' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/forgot-password[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\ForgotPassword'
                    )
                )
            ),
            'web-user-details' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/details',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserDetails'
                    )
                )
            ),
            'web-user-reservation-detail' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/reservation/detail',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserReservation'
                    )
                )
            ),
            'web-user-notification-one' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/notification',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserNotification'
                    )
                )
            ),
            'web-user-order' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/home-order',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebHomeUserOrder'
                    )
                )
            ),
            'web-user-logout' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/logout',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebLogout'
                    )
                )
            ),
            'user-logout' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/logout',
                    'defaults' => array(
                        'controller' => 'User\Controller\Logout'
                    )
                )
            ),
            'web-user-orders' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/orders',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserOrder'
                    )
                )
            ),
            'web-user-reservation-home' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/reservation/home',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserHomeReservation'
                    )
                )
            ),
            'web-user-order-detail' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/order/detail',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserOrder'
                    )
                )
            ),
            'web-menu-bookmarks' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/menu-bookmarks',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebMenuBookmarks'
                    )
                )
            ),
            'web-restaurant-bookmarks' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/restaurant-bookmarks',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebRestaurantBookmarks'
                    )
                )
            ),
            'web-user-card' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/card[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserCreditCard'
                    )
                )
            ),
            'web-user-address' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/address[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserAddress'
                    )
                )
            ),
            'web-user-contact-detail' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/contacts[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserContactDetail'
                    )
                )
            ),
            'web-user-setting' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/setting[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserSetting'
                    )
                )
            ),
            'web-user-order-count' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/home-order-count',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserSetting'
                    )
                )
            ),
            'web-user-order-details' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/order[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserOrder'
                    )
                )
            ),
            'web-user-reservation-id' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/reservation/detail[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserReservation'
                    )
                )
            ),
            'web-user-instructions' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/instruction[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserInstruction'
                    )
                )
            ),
            'user-reservation-invitation-status' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/reservation/invitation[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserReservationInvitation'
                    )
                )
            ),
            'user-home-point' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/point',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserPoint'
                    )
                )
            ),
            'user-deals-coupons' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/deals',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserDealsCoupons'
                    )
                )
            ),
            'user-point-detail' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/point-detail',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserPointDetail'
                    )
                )
            ),
            'web-user-change-profile-image' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/change-profile-image[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserChangeProfileImage'
                    )
                )
            ),
            'web-user-friend' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/friends[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserFriend'
                    )
                )
            ),
            'web-change-password' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/change-password[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebChangePassword'
                    )
                )
            ),
            'web-user-change-name' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/change-name[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserName'
                    )
                )
            ),
            'print-deals-coupons' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/print-voucher[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserDealsCoupons'
                    )
                )
            ),

            'web-change-password' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/change-password[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebChangePassword'
                    )
                )
            ),
            'web-user-change-name' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/change-name[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserName'
                    )
                )
            ),
            'print-deals-coupons' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/print-voucher[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserDealsCoupons'
                    )
                )
            ),
            'send-invitation-reservation' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/reservation/sendinvitation',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserReservationInvitation'
                    )
                )
            ),
            'user-friend-detail' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/friend[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserFriend'
                    )
                )
            ),
            'web-restaurant-bookmark-count' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/restaurant-bookmark-count[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebRestaurantBookmarksCount'
                    )
                )
            ),
            'web-menu-bookmark-count' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/menu-bookmark-count[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebMenuBookmarksCount'
                    )
                )
            ),
            'home-deals-coupons' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/home-deals',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserHomeDealsCoupons'
                    )
                )
            ),
            'web-restaurant-review' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/restaurant-review[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserReview'
                    )
                )
            ),
            'web-restaurant-unreviewed' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/restaurant-unreviewed[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserUnReviewed'
                    )
                )
            ),
            'web-user-points' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/userpoints',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserPoint'
                    )
                )
            ),
            'web-restaurant-review-count' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/restaurant-review-count',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserReviewCount'
                    )
                )
            ),
            'web-user-friend-invite' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/invite-friends',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserFriend'
                    )
                )
            ),
            'change-password' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/change-password[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebChangePassword'
                    )
                )
            ),
            'web-total-munchado-points' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/total-munchado-points[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebTotalMunchadoPoints'
                    )
                )
            ),
            'web-unreviewed-order-details' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/web-unreviewed-orderdetails[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUnreviewedOrderDetails'
                    )
                )
            ),
            'web-restaurant-search' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/web-restaurant-search[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebRestaurantSearch'
                    )
                )
            ),
            'web-food-search' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/web-food-search[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebFoodSearch'
                    )
                )
            ),
            'web-resrvation-review' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/web-reservation-review[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebReservationReview'
                    )
                )
            ),
            'web-order-review' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/web-order-review[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebOrderReview'
                    )
                )
            ),
            'user-contact' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/contact[/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserContact'
                    )
                )
            ),
            'user-login-google-contact' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/googlecontact[/:id]',
                    'constraints' => array(
                        'id' => 'googleauthenticate'
                    ),
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserContact'
                    )
                )
            ),
            'user-login-microsoft' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/microsoftcontact[/:id]',
                    'constraints' => array(
                        'id' => 'microsoftauthenticate'
                    ),
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserContact'
                    )
                )
            ),
            'web-review-preview' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/web-review-preview',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebReviewPreview'
                    )
                )
            ),
            'user-login-google' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/googlelogin[/:id]',
                    'constraints' => array(
                        'id' => 'googleauthenticate'
                    ),
                    'defaults' => array(
                        'controller' => 'User\Controller\WebLogin'
                    )
                )
            ),
            'web-user-images' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/image',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserImage'
                    )
                )
            ),
            'web-user-add-card' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/cards',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserAddCard'
                    )
                )
            ),
            'web-user-getall-card' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/cards',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserAddCard'
                    )
                )
            ),
            'web-user-delete-card' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/cards[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserAddCard'
                    )
                )
            ),
            'user-login-yahoo-contact' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/yahoocontact[/:id]',
                    'constraints' => array(
                        'id' => 'yahootauthenticate'
                    ),
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserContact'
                    )
                )
            ),
            'web-tutorial' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/tutorial[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebTutorial'
                    )
                )
            ),
            'web-update-email' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/email/update[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUpdateEmail'
                    )
                )
            ),
            'web-captcha' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/captcha[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebCaptcha'
                    )
                )
            ),
            'Web-user-update-notification' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/notification[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserNotification'
                    )
                )
            ),
            'myordersearch' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/myordersearch[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\MyOrderSearch'
                    )
                )
            ),
            'change-password' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/changepassword[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\ChangePassword'
                    )
                )
            ),
            'food-search' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/foodsearch[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\FoodSearch'
                    )
                )
            ),
            'accept-invitation' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/accepted[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebFriendshipAccepted'
                    )
                )
            ),
            'accept-invitation' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/accepte[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebFriendshipAccepted'
                    )
                )
            ),
            'assign-point-past-order' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/assignpoint[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebPointPastOrder'
                    )
                )
            ),
            'reservation-invitation-accepted' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/reservationaccepted[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebReservationAccepted'
                    )
                )
            ),
            'assign-point-past-reservation' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/assignresevationpoint[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebPointPastReservation'
                    )
                )
            ),
            'reservation-invitation-decline' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/reservationdecline[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebReservationDecline'
                    )
                )
            ),
            'restaurant-food-socialshare' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/socialshare',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserSocialSharePoint'
                    )
                )
            ),
            'user-friend' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/friends[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserFriend'
                    )
                )
            ),
            'accept-friendship-invitation' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/acceptfriendship[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\FriendshipAccepted'
                    )
                )
            ),
            'decline-friendship-invitation' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/declinefriendship[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\FriendshipDecline'
                    )
                )
            ),
            'unreview-user-restaurant' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/unreviewedrestaurant[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\unreviewedTriedRestaurant'
                    )
                )
            ),
            'restaurant-search' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/review-restaurant-search[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\ReviewRestaurantSearch'
                    )
                )
            ),
            'sugested-friend' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/sugestedfriend[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\SugestedFriendList'
                    )
                )
            ),
            'reservation-search' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/reservationsearch[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\MyReservationSearch'
                    )
                )
            ),
            'user-profile-image' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/profilephoto[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserChangeProfileImage'
                    )
                )
            ),
            'user-feed' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/feed[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserFeed'
                    )
                )
            ),
            'user-tip' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/tip[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserTip'
                    )
                )
            ),
            'user-card-validation' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/validatecard[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\ValidateCreditCard'
                    )
                )
            ),
            'social-media-user-details' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/usersocialdetails[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\SocialMediaUserDetails'
                    )
                )
            ),
            'feed' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/feed[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserActivityFeed'
                    )
                )
            ),
            'final-checkin' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/finalcheckin[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\FinalChecking'
                    )
                )
            ),
            'web-email-subscription' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/emailsubscription',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebEmailSubscription'
                    )
                )
            ),
            're-order-detail' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/reorderdetail[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\ReOrderDetails'
                    )
                )
            ),
            'feed-comment' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/feedcomment[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\FeedComment'
                    )
                )
            ),
            'facebook-post' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/facebookpost',
                    'defaults' => array(
                        'controller' => 'User\Controller\FacebookPost'
                    )
                )
            ),
            'facebook-share' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/facebookshare',
                    'defaults' => array(
                        'controller' => 'User\Controller\FacebookShare'
                    )
                )
            ),
            'my-muncher' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/mymuncher[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\MyMuncher'
                    )
                )
            ),
            'my-checkin' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/checkin[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserCheckin'
                    )
                )
            ),
            'user-action-setting' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/actionsetting[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\userActionSetting'
                    )
                )
            ),
            'user-wallpaper' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/wallpaper[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\userWallPaper'
                    )
                )
            ),
            'assign-point-to-past-order' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/assignpoint[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\PointPastOrder'
                    )
                )
            ),
            'associate-user' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/assignref[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\InvitationAssociation'
                    )
                )
            ),
            'user-referrel-data' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/referral',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserReferral'
                    )
                )),
            'redeeption' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/redemption[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebRedemptionCashBack'
                    )
                )
            ),
            'redeeption-opennight' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/redemptionnight[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebRedemptionOpenNight'
                    )
                )
            ),
            'app-send-sms-mail' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/appsendsmsmail[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\AppSendSmsMail'
                        )
                    )
                ),
            'user-referral-code' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/referralcode[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserReferralCode'
                        )
                    )
                ),
            'api-user-referrel-data' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/referral',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserReferral'
                    )
                )),
            'api-user-referrel-template-data' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/referraltemplate',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserReferralTemplate'
                    )
                )),
            'api-user-friend-invite-data' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/friendinvitetemplate',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserFriendInviteTemplate'
                    )
                )),
            'api-user-reservation-invite-data' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/reservationinvitetemplate',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserReservationInviteTemplate'
                    )
                )),
            'api-user-short-url-data' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/shorturl',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserShortUrl'
                    )
                )),
            'wapi-dollor-five-off' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/dollarfiveoff',
                    'defaults' => array(
                        'controller' => 'User\Controller\AssignDollarFive'
                    )
                )),
            'wapi-registration-with-sms' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/smsregistration[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebRegistrationWithSms'
                    )
                )),
             'api-registration-with-sms' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/smsregistration[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\RegistrationWithSms'
                    )
                )),
            'wapi-loyality-code' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/loyalitycode[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebLoyalityProgramCode'
                    )
                )),
            'api-loyality-code' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/loyalitycode[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\LoyalityProgramCode'
                    )
                )),
            'wapi-user-deal-read' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/dealread[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebDealsCouponRead'
                    )
                )),
            'api-user-deal-read' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/dealread[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebDealsCouponRead'
                    )
                )),
            
            'api-user-dine_and_more_referrel-template-data' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/dinemorereferraltemplate',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserDineMoreReferralTemplate'
                    )
                )),
            'wapi-request-server_for_user_registration' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/serveruserregistration',
                    'defaults' => array(
                        'controller' => 'User\Controller\RegistrationWithSms'
                    )
                )),
            'user-details-bulk' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/reg[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\UserDetailsBulk'
                    )
                )),
            'wapi-user-referral-dinemore' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/referraldinemore[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebReferralDineAndMore'
                    )
                )),
            'user-referral-dinemore' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/referraldinemore[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebReferralDineAndMore'
                    )
                )),
            'web-re-order-detail' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/userreorder[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebReOrder'
                    )
                )
            ),
            'api-check-twitter-account' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/chksocial',
                    'defaults' => array(
                        'controller' => 'User\Controller\CheckTwitterAccount'
                    )
                )
            ),
            'wapi-munchado-career' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/career[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\MunchAdoCareer'
                    )
                )
            ),
            'user-sms-offer' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/smsoffer',
                    'defaults' => array(
                        'controller' => 'User\Controller\SmsOffer'
                    )
                )
            ),
            'web-user-reorder-details' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/reorder[/:id]',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebUserReorder'
                    )
                )
            ),
            'web-ma-reservation-invitation' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/mareservationinvitation',
                    'defaults' => array(
                        'controller' => 'User\Controller\WebMaReservationInvitation'
                    )
                )
            ),
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'User\Controller\Reservation' => 'User\Controller\ReservationController',
            'User\Controller\MyReservation' => 'User\Controller\MyReservationController',
            'User\Controller\WebUserReservation' => 'User\Controller\WebUserReservationController',
            'User\Controller\Login' => 'User\Controller\LoginController',
            'User\Controller\WebLogin' => 'User\Controller\WebLoginController',
            'User\Controller\DealsCoupons' => 'User\Controller\DealsCouponsController',
            'User\Controller\Coupons' => 'User\Controller\CouponsController',
            'User\Controller\Registration' => 'User\Controller\RegistrationController',
            'User\Controller\Review' => 'User\Controller\ReviewController',
            'User\Controller\Usermenureview' => 'User\Controller\UsermenureviewController',
            'User\Controller\Location' => 'User\Controller\LocationController',
            'User\Controller\SendInvitation' => 'User\Controller\SendInvitationController',
            'User\Controller\ReportProblem' => 'User\Controller\ReportProblemController',
            'User\Controller\UserAddress' => 'User\Controller\UserAddressController',
            'User\Controller\CreditCard' => 'User\Controller\CreditCardController',
            'User\Controller\UserRestaurantImage' => 'User\Controller\UserRestaurantImageController',
            'User\Controller\WebUserDetails' => 'User\Controller\WebUserDetailsController',
            'User\Controller\OrderPlace' => 'User\Controller\OrderPlaceController',
            'User\Controller\WebHomeUserOrder' => 'User\Controller\WebHomeUserOrderController',
            'User\Controller\UserNotification' => 'User\Controller\UserNotificationController',
            'User\Controller\WebUserNotification' => 'User\Controller\WebUserNotificationController',
            'User\Controller\ForgotPassword' => 'User\Controller\ForgotPasswordController',
            'User\Controller\WebForgotPassword' => 'User\Controller\WebForgotPasswordController',
            'User\Controller\Logout' => 'User\Controller\LogoutController',
            'User\Controller\WebLogout' => 'User\Controller\WebLogoutController',
            'User\Controller\WebUserOrder' => 'User\Controller\WebUserOrderController',
            'User\Controller\UserOrder' => 'User\Controller\UserOrderController',
            'User\Controller\WebUserHomeReservation' => 'User\Controller\WebUserHomeReservationController',
            'User\Controller\WebMenuBookmarks' => 'User\Controller\WebMenuBookmarksController',
            'User\Controller\WebRestaurantBookmarks' => 'User\Controller\WebRestaurantBookmarksController',
            'User\Controller\WebUserCreditCard' => 'User\Controller\WebUserCreditCardController',
            'User\Controller\WebUserAddress' => 'User\Controller\WebUserAddressController',
            'User\Controller\WebUserContactDetail' => 'User\Controller\WebUserContactDetailController',
            'User\Controller\WebUserSetting' => 'User\Controller\WebUserSettingController',            
            'User\Controller\WebUserReservation' => 'User\Controller\WebUserReservationController',
            'User\Controller\WebUserInstruction' => 'User\Controller\WebUserInstructionController',
            'User\Controller\UserPoints' => 'User\Controller\UserPointsController',
            'User\Controller\PointSource' => 'User\Controller\PointSourceController',
            'User\Controller\UserNotification' => 'User\Controller\UserNotificationController',
            'User\Controller\UserCurrentNotification' => 'User\Controller\UserCurrentNotificationController',
            'User\Controller\ReadNotificationList' => 'User\Controller\ReadNotificationListController',
            'User\Controller\UserDetails' => 'User\Controller\UserDetailsController',
            'User\Controller\UserReviewsList' => 'User\Controller\UserReviewsListController',
            'User\Controller\MenuBookmarks' => 'User\Controller\MenuBookmarksController',
            'User\Controller\RestaurantBookmarks' => 'User\Controller\RestaurantBookmarksController',
            'User\Controller\UserReview' => 'User\Controller\UserReviewController',
            'User\Controller\UserProfileUpdate' => 'User\Controller\UserProfileUpdateController',
            'User\Controller\UserNotificationSettings' => 'User\Controller\UserNotificationSettingsController',
            'User\Controller\ReservationAgain' => 'User\Controller\ReservationAgainController',
            'User\Controller\InvitedFriendList' => 'User\Controller\InvitedFriendListController',
            'User\Controller\WebUserPoint' => 'User\Controller\WebUserPointController',
            'User\Controller\WebUserPointDetail' => 'User\Controller\WebUserPointDetailController',
            'User\Controller\WebUserReservationInvitation' => 'User\Controller\WebUserReservationInvitationController',
            'User\Controller\WebUserPoint' => 'User\Controller\WebUserPointController',
            'User\Controller\WebUserDealsCoupons' => 'User\Controller\WebUserDealsCouponsController',
            'User\Controller\WebUserChangeProfileImage' => 'User\Controller\WebUserChangeProfileImageController',
            'User\Controller\WebChangePassword' => 'User\Controller\WebChangePasswordController',
            'User\Controller\WebUserName' => 'User\Controller\WebUserNameController',
            'User\Controller\WebUserDealsCoupons' => 'User\Controller\WebUserDealsCouponsController',
            'User\Controller\WebUserHomeDealsCoupons' => 'User\Controller\WebUserHomeDealsCouponsController',
            'User\Controller\WebRestaurantBookmarksCount' => 'User\Controller\WebRestaurantBookmarksCountController',
            'User\Controller\WebMenuBookmarksCount' => 'User\Controller\WebMenuBookmarksCountController',
            'User\Controller\WebUserFriend' => 'User\Controller\WebUserFriendController',
            //'User\Controller\WebReservationInvitation' => 'User\Controller\WebReservationInvitationController',
            //'User\Controller\WebUserReservationInvitation' => 'User\Controller\WebUserReservationInvitationController',
            //'User\Controller\WebUserFriend' => 'User\Controller\WebUserFriendController',
            'User\Controller\WebUserReview' => 'User\Controller\WebUserReviewController',
            'User\Controller\WebUserUnReviewed' => 'User\Controller\WebUserUnReviewedController',
            'User\Controller\WebUserPoint' => 'User\Controller\WebUserPointController',
            'User\Controller\WebUserReviewCount' => 'User\Controller\WebUserReviewCountController',
            'User\Controller\WebTotalMunchadoPoints' => 'User\Controller\WebTotalMunchadoPointsController',
            'User\Controller\WebUnreviewedOrderDetails' => 'User\Controller\WebUnreviewedOrderDetailsController',
            'User\Controller\WebRestaurantSearch' => 'User\Controller\WebRestaurantSearchController',
            'User\Controller\WebFoodSearch' => 'User\Controller\WebFoodSearchController',
            'User\Controller\WebReservationReview' => 'User\Controller\WebReservationReviewController',
            'User\Controller\WebOrderReview' => 'User\Controller\WebOrderReviewController',
            'User\Controller\WebUserContact' => 'User\Controller\WebUserContactController',
            'User\Controller\WebReviewPreview' => 'User\Controller\WebReviewPreviewController',
            'User\Controller\WebUserImage' => 'User\Controller\WebUserImageController',
            'User\Controller\WebUserAddCard' => 'User\Controller\WebUserAddCardController',
            'User\Controller\WebTutorial' => 'User\Controller\WebTutorialController',
            'User\Controller\WebUpdateEmail' => 'User\Controller\WebUpdateEmailController',
            'User\Controller\WebCaptcha' => 'User\Controller\WebCaptchaController',
            'User\Controller\MyOrderSearch' => 'User\Controller\MyOrderSearchController',
            'User\Controller\ChangePassword' => 'User\Controller\ChangePasswordController',
            'User\Controller\FoodSearch' => 'User\Controller\FoodSearchController',
            'User\Controller\WebFriendshipAccepted' => 'User\Controller\WebFriendshipAcceptedController',
            'User\Controller\WebPointPastOrder' => 'User\Controller\WebPointPastOrderController',
            'User\Controller\WebReservationAccepted' => 'User\Controller\WebReservationAcceptedController',
            'User\Controller\WebPointPastReservation' => 'User\Controller\WebPointPastReservationController',
            'User\Controller\WebReservationDecline' => 'User\Controller\WebReservationDeclineController',
            'User\Controller\WebUserSocialSharePoint' => 'User\Controller\WebUserSocialSharePointController',
            'User\Controller\UserFriend' => 'User\Controller\UserFriendController',
            'User\Controller\FriendshipAccepted' => 'User\Controller\FriendshipAcceptedController',
            'User\Controller\FriendshipDecline' => 'User\Controller\FriendshipDeclineController',
            'User\Controller\unreviewedTriedRestaurant' => 'User\Controller\unreviewedTriedRestaurantController',
            'User\Controller\ReviewRestaurantSearch' => 'User\Controller\ReviewRestaurantSearchController',
            'User\Controller\SugestedFriendList' => 'User\Controller\SugestedFriendListController',
            'User\Controller\MyReservationSearch' => 'User\Controller\MyReservationSearchController',
            'User\Controller\UserCheckin' => 'User\Controller\UserCheckinController',
            'User\Controller\UserChangeProfileImage' => 'User\Controller\UserChangeProfileImageController',
            'User\Controller\UserFeed' => 'User\Controller\UserFeedController',
            'User\Controller\UserTip' => 'User\Controller\UserTipController',
            'User\Controller\ValidateCreditCard' => 'User\Controller\ValidateCreditCardController',
            'User\Controller\SocialMediaUserDetails' => 'User\Controller\SocialMediaUserDetailsController',
            'User\Controller\UserActivityFeed' => 'User\Controller\UserActivityFeedController',
            'User\Controller\FinalChecking' => 'User\Controller\FinalCheckingController',
            'User\Controller\WebEmailSubscription' => 'User\Controller\WebEmailSubscriptionController',
            'User\Controller\ReOrderDetails' => 'User\Controller\ReOrderDetailsController',
            'User\Controller\FeedComment' => 'User\Controller\FeedCommentController',
            'User\Controller\FacebookPost' => 'User\Controller\FacebookPostController',
            'User\Controller\FacebookShare' => 'User\Controller\FacebookShareController',
            'User\Controller\MyMuncher' => 'User\Controller\MyMuncherController',
            'User\Controller\UserRecents' => 'User\Controller\UserRecentsController',
            'User\Controller\userActionSetting' => 'User\Controller\UserActionSettingController',
            'User\Controller\userWallPaper' => 'User\Controller\UserWallPaperController',
            'User\Controller\PointPastOrder' => 'User\Controller\PointPastOrderController',
            'User\Controller\InvitationAssociation' => 'User\Controller\InvitationAssociationController',
            'User\Controller\WebUserReferral' => 'User\Controller\WebUserReferralController',
            'User\Controller\WebRedemptionCashBack' => 'User\Controller\WebRedemptionCashBackController',
            'User\Controller\WebRedemptionOpenNight' => 'User\Controller\WebRedemptionOpenNightController',
            'User\Controller\AppSendSmsMail'=>'User\Controller\AppSendSmsMailController',
            'User\Controller\UserReferralCode'=>'User\Controller\UserReferralCodeController',
            'User\Controller\UserReferral' => 'User\Controller\UserReferralController',
            'User\Controller\UserReferralTemplate'=>'User\Controller\UserReferralTemplateController',
            'User\Controller\UserFriendInviteTemplate'=>'User\Controller\UserFriendInviteTemplateController',
            'User\Controller\UserShortUrl'=>'User\Controller\UserShortUrlController',
            'User\Controller\AssignDollarFive'=>'User\Controller\AssignDollarFiveController',
            'User\Controller\UserReservationInviteTemplate'=>'User\Controller\UserReservationInviteTemplateController',
            'User\Controller\WebRegistrationWithSms'=>'User\Controller\WebRegistrationWithSmsController',
            'User\Controller\RegistrationWithSms'=>'User\Controller\RegistrationWithSmsController',
            'User\Controller\WebLoyalityProgramCode'=>'User\Controller\WebLoyalityProgramCodeController',
            'User\Controller\LoyalityProgramCode'=>'User\Controller\LoyalityProgramCodeController',
            'User\Controller\WebDealsCouponRead'=>'User\Controller\WebDealsCouponReadController',
            'User\Controller\UserDineMoreReferralTemplate'=>'User\Controller\UserDineMoreReferralTemplateController',
            'User\Controller\UserDetailsBulk'=>'User\Controller\UserRegistrationInBulkController',
            'User\Controller\WebReferralDineAndMore'=>'User\Controller\WebReferralDineAndMoreController',
            'User\Controller\WebReOrder'=>'User\Controller\WebReOrderController',
            'User\Controller\CheckTwitterAccount'=>'User\Controller\CheckTwitterAccountController',
            'User\Controller\MunchAdoCareer'=>'User\Controller\MunchAdoCareerController',
            'User\Controller\SmsOffer'=>'User\Controller\SmsOfferController',
            'User\Controller\WebUserReorder' => 'User\Controller\WebUserReorderController',
            'User\Controller\WebMaReservationInvitation'=>"User\Controller\WebMaReservationInvitationController"
        )
    ),
    'view_manager' => array(
        'template_map' => array(
            'email-layout/default' => __DIR__ . '/../Mails/User/layouts/default.phtml',
            'email-layout/default_new' => __DIR__ . '/../Mails/User/layouts/default_new.phtml',
            'email-layout/default_register' => __DIR__ . '/../Mails/User/layouts/default_register.phtml',
            'email-layout/default_app' => __DIR__ . '/../Mails/User/layouts/default_app.phtml',
            'email-layout/default_android_app' => __DIR__ . '/../Mails/User/layouts/default_android_app.phtml',
            'email-template/send_Invitation' => __DIR__ . '/../Mails/User/templates/send_Invitation.phtml',
            'email-template/forgot-password' => __DIR__ . '/../Mails/User/templates/forgot_password.phtml',
            'email-template/friends-Invitation' => __DIR__ . '/../Mails/User/templates/03_Joe-Gave-Us-Your-Email.-That\'s-Cool-Right.phtml',
            'email-template/friends-reservation-Invitation' => __DIR__ . '/../Mails/User/templates/09_Someone-Wants-To-Grab-Food-With-You.phtml',
            'email-template/send-cancel-reservation' => __DIR__ . '/../Mails/User/templates/23_Bad-News-About-a-Munchado-Reservation.phtml',
            'email-template/send-cancel-reservation-owner' => __DIR__ . '/../Mails/User/templates/23_Bad-News-About-a-MunchAdo-Reservation_Owner.phtml',
            'email-template/send-cancel-order-user' => __DIR__ . '/../Mails/User/templates/14_Your-Pre-Sceduled-Order-Has-Been-Pre-Canceled.phtml',
            'email-template/modify-reservation' => __DIR__ . '/../Mails/User/templates/06_Your-Modifications-Were-Successful.phtml',
            'email-template/place-order-takeout' => __DIR__ . '/../Mails/User/templates/order-takeout.phtml',
            'email-template/place-order-delivery' => __DIR__ . '/../Mails/User/templates/order-delivery.phtml',
            'email-template/user-registration' => __DIR__ . '/../Mails/User/templates/01_Welcome-Friend.phtml',
            'email-template/user-reservation-confirmation' => __DIR__ . '/../Mails/User/templates/reservation-placed.phtml',
            'email-template/munchado-customer-reservation-confirmation' => __DIR__ . '/../Mails/User/templates/22_Reservation-From-a-MunchAdo-Customer.phtml',
            'email-template/social-media-registration' => __DIR__ . '/../Mails/User/templates/02_Weclome-Social-Media-Savant.phtml',
            'email-template/modify-reservation-to-friends' => __DIR__ . '/../Mails/User/templates/31_Modification_Reservation_Invitee.phtml',
            'email-template/modify-reservation-to-owner' => __DIR__ . '/../Mails/User/templates/32_One_of_Your_Reservations_Has_Changed.phtml',
            'email-template/review-mail-to-owner' => __DIR__ . '/../Mails/User/templates/27_Someone-Posted-a-New-Review-for-You-on-HungryBuzz.phtml',
            'email-template/send-cancel-reservation-friends' => __DIR__ . '/../Mails/User/templates/EPIC-Fail.phtml',
            'email-template/preorder-user-mail' => __DIR__ . '/../Mails/User/templates/34_Planning-Ahead-We-see.phtml',
            'email-template/preorder-user-tackout-mail' => __DIR__ . '/../Mails/User/templates/35_Planning-Ahead-We-see-tackout.phtml',
            'email-template/food-there' => __DIR__ . '/../Mails/User/templates/Foods-There.phtml',
            'email-template/owners-response' => __DIR__ . '/../Mails/User/templates/Owner-response.phtml',
            'email-template/order-detail-service-provider' => __DIR__ . '../Mails/User/templates',
            'email-template/place-order-delivery-service-provider' => __DIR__ . '/../Mails/User/templates/order-delivery-service-provider.phtml',
            'email-template/preorder-user-mail-service-provider' => __DIR__ . '/../Mails/User/templates/34_Planning-Ahead-We-see-service-provider.phtml',
            'email-template/Pre-Ordered_Pre-Reservation_Pre-Approved' => __DIR__ . '/../Mails/User/templates/Pre-Ordered_Pre-Reservation_Pre-Approved.phtml',
            'email-template/New_Pre-Paid_Reservation' => __DIR__ . '/../Mails/User/templates/New_Pre-Paid_Reservation.phtml',
            'email-template/You_Us_Them_And_Food' => __DIR__ . '/../Mails/User/templates/You_Us_Them_And_Food.phtml',
            'email-template/Came_In_Like_A_Wrecking_Ball' => __DIR__ . '/../Mails/User/templates/Came_In_Like_A_Wrecking_Ball.phtml',
            'email-template/Turned_Down_For_What' => __DIR__ . '/../Mails/User/templates/Turned_Down_For_What.phtml',
            'email-template/Its_a_date_And_time_And_food' => __DIR__ . '/../Mails/User/templates/Its_a_date_And_time_And_food.phtml',
            'email-layout/default_email_subscription' => __DIR__ . '/../Mails/User/layouts/default_email_subscription.phtml',
            'email-template/Email-Subscription' => __DIR__ . '/../Mails/User/templates/01_Email-Subscription.phtml',
            'email-template/Email-Subscription-registration' => __DIR__ . '/../Mails/User/templates/02_Email-Subscription-registration.phtml',
            'email-template/Email-Subscription-more-from-munchado' => __DIR__ . '/../Mails/User/templates/03_Email-Subscription-more-from-munchado.phtml',
            'email-template/five-doller-on-registration' => __DIR__ . '/../Mails/User/templates/01_five-doller-on-registration.phtml',
            'email-layout/05_registration' => __DIR__ . '/../Mails/User/layouts/05_registration.phtml',
            'email-template/05_So_They_Get_Another_Coupon' => __DIR__ . '/../Mails/User/templates/05_So_They_Get_Another_Coupon.phtml',
            'email-layout/default_email_subscription_without_10' => __DIR__ . '/../Mails/User/layouts/default_email_subscription_without_10.phtml',
            'email-template/05_Your-Reservation-has-been-reserved' => __DIR__ . '/../Mails/User/templates/05_Your-Reservation-has-been-reserved.phtml',
            'email-template/is_buying_food_you_in' => __DIR__ . '/../Mails/User/templates/is_buying_food_you_in.phtml',
            'email-template/redemption_cashback' => __DIR__ . '/../Mails/User/templates/redemption_cashback.phtml',
            'email-template/redemption_opennight' => __DIR__ . '/../Mails/User/templates/redemption_opennight.phtml',
            'email-template/newcard' => __DIR__ . '/../Mails/User/templates/newcard.phtml',
            'email-template/edu_subscriber' => __DIR__ . '/../Mails/User/templates/edu_subscriber.phtml',
            'email-template/inviteemail' => __DIR__ . '/../Mails/User/templates/inviteemail.phtml',
            'email-template/To_Go_For_30_From_Munch_Ado' => __DIR__ . '/../Mails/User/templates/To_Go_For_30_From_Munch_Ado.phtml',
            'email-template/Turn_Your_5_Cash_Back_into_Another_30' => __DIR__ . '/../Mails/User/templates/Turn_Your_5_Cash_Back_into_Another_30.phtml',
            'email-template/emailawarding' => __DIR__ . '/../Mails/User/templates/emailawarding.phtml',
            'email-layout/default_emailer' => __DIR__ . '/../Mails/User/layouts/default_emailer.phtml',
            'email-template/app_released' => __DIR__ . '/../Mails/User/templates/app_released.phtml',
            'email-template/app_notification_subscriber' => __DIR__ . '/../Mails/User/templates/app_notification_subscriber.phtml',
            'email-template/android_app_notify' => __DIR__ . '/../Mails/User/templates/android_app_notify.phtml',
            'email-template/Turned_Down_For_What_normal_reservation' => __DIR__ . '/../Mails/User/templates/Turned_Down_For_What_normal_reservation.phtml',
            'email-template/agreed_to_join_munchado'=>__DIR__ . '/../Mails/User/templates/agreed_to_join_munchado.phtml',
            'email-template/promo-alert' => __DIR__ . '/../Mails/User/templates/02_to_users_coupon-expires.phtml',
            'email-template/you_did_it_again' => __DIR__ . '/../Mails/User/templates/you_did_it_again.phtml',
            'email-layout/you_did_it_again' => __DIR__ . '/../Mails/User/layouts/you_did_it_again.phtml',
            'email-template/loyaltyregister' => __DIR__ . '/../Mails/User/templates/loyaltyRegister.phtml',
            'email-layout/default_dineandmore' => __DIR__ . '/../Mails/User/layouts/default_dineandmore.phtml',
            'email-template/Welcome_To_Restaurant_Dine_More_Rewards_New_User_Password' => __DIR__ . '/../Mails/User/templates/Welcome_To_Restaurant_Dine_More_Rewards_New_User_Password.phtml',
            'email-template/Welcome_To_Restaurant_Dine_More_Rewards_Exist_User' => __DIR__ . '/../Mails/User/templates/Welcome_To_Restaurant_Dine_More_Rewards_Exist_User.phtml',
            'email-template/Welcome_To_Restaurant_Dine_More_Rewards_From_Site_App' => __DIR__ . '/../Mails/User/templates/Welcome_To_Restaurant_Dine_More_Rewards_From_Site_App.phtml',
            'email-layout/referral' => __DIR__ . '/../Mails/User/layouts/referral.phtml',
            'email-template/join_at_munchado' => __DIR__ . '/../Mails/User/templates/join_at_munchado.phtml',
            'email-template/server-registration' => __DIR__ . '/../Mails/User/templates/01_Welcome-server-registration.phtml',
            'email-layout/default_server_register' => __DIR__ . '/../Mails/User/layouts/default_server_register.phtml',
            'email-layout/default_update_reservation'=>__DIR__ . '/../Mails/User/layouts/default_update_reservation.phtml',
            'email-template/munchado_career' => __DIR__ . '/../Mails/User/templates/career.phtml',
            'email-layout/default_career'=>__DIR__ . '/../Mails/User/layouts/default_career.phtml',
            'email-layout/default_career_bravvura'=>__DIR__ . '/../Mails/User/layouts/default_career_bravvura.phtml',
            'email-template/user-snag-a-spot-placed' => __DIR__ . '/../Mails/User/templates/user-snag-a-spot-placed.phtml',
            'email-template/sms_offer_mail' => __DIR__ . '/../Mails/User/templates/sms_offer_mail.phtml',
            'email-template/registration_from_micro_site' => __DIR__ . '/../Mails/MA/templates/registration_from_micro_site.phtml',
            'email-layout/ma_default' => __DIR__ . '/../Mails/MA/layouts/default.phtml',
            'email-template/registration_from_micro_site_with_dine_more_code' => __DIR__ . '/../Mails/MA/templates/registration_from_micro_site_with_dine_more_code.phtml',
            'email-template/registration_from_micro_site_with_dine_more_code_exist_user'=> __DIR__ . '/../Mails/MA/templates/registration_from_micro_site_with_dine_more_code_exist_user.phtml',
            'email-template/ma_micro_order_confirm'=>__DIR__ . '/../Mails/MA/templates/ma_micro_order_confirm.phtml',
            'email-template/microsite_carrier'=>__DIR__ . '/../Mails/MA/templates/microsite_carrier.phtml',
            'email-template/microsite_alberto_enquiry'=>__DIR__ . '/../Mails/MA/templates/microsite_alberto_enquiry.phtml',
            'email-layout/ma_alberto_default' => __DIR__ . '/../Mails/MA/layouts/alberto_default.phtml',
            'email-template/ma_forgot_password' => __DIR__ . '/../Mails/MA/templates/ma_forgot_password.phtml',
            'email-template/ma_friends-reservation-Invitation'=>__DIR__ . '/../Mails/MA/templates/ma_Someone-Wants-To-Grab-Food-With-You.phtml',
            'email-template/ma_micro_reservation_confirm'=>  __DIR__ . '/../Mails/MA/templates/ma_micro_reservation_confirm.phtml',          
    
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
        'order_status' => array(
            'placed',//0
            'ordered',//1
            'confirmed',//2
            'delivered',//3
            'cancelled',//4
            'rejected',//5
            'arrived',//6
            'frozen',//7
            'ready',//8
            'archived'//9
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
        'reservation_invitation_status' => array(
            'invited' => '0',
            'accepted' => '1',
            'denied' => '2',
            'submitted' => '3'
        ),
        'dealsCoupons_status' => array(
            'live',
            'archived',
            'expired',
            'redeemed'
        ),
        'user_friends_status' => array(
            'active' => '1',
            'inactive' => '0',
            'unfriend' => '2'
        ),
        'point_source_detail' => array(
            'orderPlacedTakeout' => '1',
            'groupOrderPlaced' => '2',
            'reserveATable' => '3',
            'purchaseADealCoupon' => '4',
            'inviteFriends' => '5',
            'rateAndReview' => '6',
            'postPictures' => '7',
            'reportErrors' => '8',
            'completeProfile' => '9',
            'postOnFacebook' => '10',
            'postOnTwitter' => '11',
            'reservationAccept' => '17'
        ),
        'review_for' => array(
            '1' => 'Delivery',
            '2' => 'Takeout',
            '3' => 'Dinein'
        ),
        'on_time' => array(
            '0' => '',
            '1' => 'Yes',
            '2' => 'No'
        ),
        'fresh_prepared' => array(
            '0' => '',
            '1' => 'Yes',
            '2' => 'No'
        ),
        'as_specifications' => array(
            '0' => '',
            '1' => 'Yes',
            '2' => 'No'
        ),
        'temp_food' => array(
            '0' => '',
            '1' => 'Too cold',
            '2' => 'Just right',
            '3' => 'Too hot'
        ),
        'taste_test' => array(
            '0' => '',
            '1' => 'Horiable',
            '2' => 'Ok but could be better',
            '3' => 'Loved it'
        ),
        'services' => array(
            '0' => '',
            '1' => 'Extremly nice',
            '2' => 'Just right',
            '3' => 'Un-acceptiable Unfriendly',
        ),
        'noise_level' => array(
            '0' => '',
            '1' => 'Quite and Conversational',
            '2' => 'Normal',
            '3' => 'Loud'
        ),
        'order_again' => array(
            '0' => '',
            '1' => 'Yes',
            '2' => 'No'
        ),
        'come_back' => array(
            '0' => '',
            '1' => 'Yes',
            '2' => 'No'
        ),
        'mystery_meals_zip' => array(
            '10001',
            '10011',
            '10018',
            '10019',
            '10036'
        ),
        'address_types_street' => array(
            'street_address',
            'route',
            'premise',
            'subpremise',
            'natural_feature',
            'airport',
            'park',
            'establishment'
        ),
        'address_types_neighbourhood' => array(
            'neighborhood',
            'neighbourhood',
            'sublocality',
            'sublocality_level_5'
        ),
        'address_types_zip' => array(
            'postal_code'
        ),
        'address_types_city' => array(
            "locality"
        ),
        'muncher_identifire' => array(
            'fu_munchu' => 'fuMunchuMuncher',
            'health_nut' => 'healthNutMuncher',
            'sir_loin' => 'sirLoinMuncher',
            'vip' => 'vipMuncher',
            'home_eater' => 'homeEaterMuncher',
            'takeout_artist' => 'takeoutArtistMuncher',
            'food_pundit' => 'foodPunditMuncher',
            'munch_maven' => 'munchMavenMuncher',
            'cheesy_triangle' => 'cheesyTriangleMuncher',
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
            '&#382;' => 'z',
            '\u2019s'=> "'s"
        ),
        'redemptionSpecial' => array(
            'opennight' => 1,
            'cashback' => 2,
            'hispanicnight' => 3
        ),
        'notEmailRestriction' => array(
            'forgot-password',
            'emailawarding',
            'modify-reservation',
            'modify-reservation-to-friends',
            'send-cancel-reservation',
            'send-cancel-reservation-friends',
            'is_buying_food_you_in',
            'friends-reservation-Invitation'),
        'pointEqualDollar'=>array(1,0.01),//0 index contain point and 1 index contain dollar
    ),
    
);
