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
            'restaurant-overview' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/overview[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\Overview'
                    )
                )
            ),
            'restaurant-story' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/story[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\Story'
                    )
                )
            ),
            'restaurant-detail' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/detail[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\Detail'
                    )
                )
            ),
            'restaurant-review' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/review[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\Review'
                    )
                )
            ),
            'restaurant-menu' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/menu[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\Menu'
                    )
                )
            ),
            'web-restaurant-menu-special' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/menuspecific[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebMenuSpecificDeal'
                    )
                )
            ),
            'restaurant-gallery' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/gallery[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\Gallery'
                    )
                )
            ),
            'restaurant-addons' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/menu/addons[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\MenuAddons'
                    )
                )
            ),
            'restaurant-config' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/config[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\Config'
                    )
                )
            ),
            'restaurant-ordering' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/placeorder[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\PreOrder'
                    )
                )
            ),
            'restaurant-distance-check' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/order/distancecheck[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\DistanceCheck'
                    )
                )
            ),
            'restaurant-timeslot' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/timeslots[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\Timeslots'
                    )
                )
            ),
            'restaurant-current-time' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/current-time[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\CurrentTime'
                    )
                )
            ),
            'restaurant-closed-date' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/closed-date[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\ClosedDate'
                    )
                )
            ),
            'restaurant-deals-coupons' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/deals-coupons[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\DealsCoupons'
                    )
                )
            ),
            'restaurant-top-menu' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/topmenu[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\TopMenu'
                    )
                )
            ),
            'restaurant-review-detail' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/reviewdetail[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\ReviewDetail'
                    )
                )
            ),
            'restaurant-location' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/location[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\RestaurantLocation'
                    )
                )
            ),
            'restaurant-timeslot-next-seven-days' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/restauranttimeslot[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\RestaurantTimeSlot'
                    )
                )
            ),
            'restaurant-short-address' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/rsa[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\RestaurantShortAddress'
                    )
                )
            ),
            'restaurant-delever-to-address' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/delevertoaddress[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\RestaurantDeleverToAddress'
                    )
                )
            ),
            'restaurant-reservation-timeslot' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/reservation-timeslot[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\ReservationTimeslot'
                    )
                )
            ),
            'restaurant-reservation-category-timeslots' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/reservation-category-timeslots[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\ReservationCategoryTimeslots'
                    )
                )
            ),
            'web-restaurant-top-menu' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/topmenu[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebTopMenu'
                    )
                )
            ),
            'web-social-media-activity' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/socialmediaactivity[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebSocialMediaActivity'
                    )
                )
            ),
            'web-short-review' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/shortreview[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebShortReview'
                    )
                )
            ),
            'web-restaurant-story' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/story[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebStory'
                    )
                )
            ),
            'web-restaurant-gallery' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/gallery[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebGallery'
                    )
                )
            ),
            'web-restaurant-details' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/details[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebRestaurantGeneralDetails'
                    )
                )
            ),
            'web-restaurant-overview' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/overview[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebOverview'
                    )
                )
            ),
            'web-review' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/review[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebReview'
                    )
                )
            ),
            'restaurant-web-menu' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/menu[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebMenu'
                    )
                )
            ),
            'restaurant-report-abuse' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/report-abuse',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebReportAbuse'
                    )
                )
            ),
            'menu-addons' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/menu-addons[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebAddons'
                    )
                )
            ),
            'place-order' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/place-order',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebOrder'
                    )
                )
            ),
            'web-deals-coupons' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/deals-coupons[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebDealsCoupons'
                    )
                )
            ),
            'web-user-feedback' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/user-feedback',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebUserFeedback'
                    )
                )
            ),
            'web-restaurant-timeslot' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/timeslot[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebRestaurantTimeSlot'
                    )
                )
            ),
            'web-reservation' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/reservation[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebReservation'
                    )
                )
            ),
            'web-reservation-invitation' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/reserve-invite[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebReservationInvite'
                    )
                )
            ),
            'web-owners-response' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/owners-response',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebOwnersResponse'
                    )
                )
            ),
            'web-owners-login' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/owners-login',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebOwnersLogin'
                    )
                )
            ),
            'web-owners-logout' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/owners-logout',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebOwnersLogin'
                    )
                )
            ),
            'web-friend-action-on-restaurant' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/friendaction[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebFriendActionOnRestaurant'
                    )
                )
            ),
            'web-menu-deals' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/menu/deals-coupons[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebMenuDeals'
                    )
                )
            ),
            'web-operating-hours' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/operations[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebOperatingHours'
                    )
                )
            ),
            'web-restaurant-timings' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/timings[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebRestaurantTimings'
                    )
                )
            ),
            'lat-long-calculation' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/latlogdistance[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebDistanceCalculation'
                    )
                )
            ),
            'web-delivery-check' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/candeliver[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebDeliveryCheck'
                    )
                )
            ),
            'point-past-reservation' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/assignpoint[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebPointPastReservation'
                    )
                )
            ),
            'web-reservationedit-timeslot' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/reservationedit/timeslot[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebReservationEditTimeSlot'
                    )
                )
            ),
            'web-restaurant-count' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurantseo',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebRestaurantSeo'
                    )
                )
            ),
            'web-restaurant-domain' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/domain/:id',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebRestaurantDomain'
                    )
                )
            ),
            'web-delivery-price' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/deliveryprice',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebDeliveryPrice'
                    )
                )
            ),
            'web-munch-serviceprovider' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/serviceprovider',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebServiceProvider'
                    )
                )
            ),
            'web-promocodes' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/user/promocodes',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebUserPromocodes'
                    )
                )
            ),
            'user-feedback' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/userfeedback',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\UserFeedback'
                    )
                )
            ),
            'operating-hours' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/operations[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\OperatingHours'
                    )
                )
            ),
            'web-restaurant-day-date' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/restaurantdaydate[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebOperatingDayDate'
                    )
                )
            ),
            'promocodes' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/user/promocodes',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\UserPromocodes'
                    )
                )
            ),
            'social-proofing' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/socialproofing[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\SocialProofing'
                    )
                )
            ),
            'menu-social-proofing' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/menusocialproofing[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\MenuSocialProofing'
                    )
                )
            ),
            'delivery-check' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/candeliver[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\DeliveryCheck'
                    )
                )
            ),
            'featured-restaurant' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/featured[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebFeaturedRestaurant'
                    )
                )
            ),
            'point-to-past-reservation' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/assignpoint[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\PointPastReservation'
                    )
                )
            ),
            'sweepatakes-restaurant' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/sweepstakes[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebSweepstakesRestaurant'
                    )
                )
            ),
            'web-restaurantname-loyality-code' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/code[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebRestaurantNameLoyalityCode'
                    )
                )
            ),
            'restaurantname-loyality-code' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/code[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\RestaurantNameLoyalityCode'
                    )
                )
            ),
            'restaurant-dinenmore' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/dinenmore[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\DinenmoreDeal'
                    )
                )
            ),
            'restaurant-menu-special' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/menuspecific[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\MenuSpecificDeal'
                    )
                )
            ),
            'restaurant-menu-new' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/menunew[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\MenuNew'
                    )
                )
            ),
            'dinenmore-restaurant-details' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/dinenmorelist[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\DinenmoreRestaurantsDetails'
                    )
                )
            ),
            'restaurant-deals' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/deals[/:id]',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\RestaurantDeals'
                    )
                )
            ),
            'curated-list' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/restaurant/curatedlist',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\CuratedList'
                    )
                )
            ),
            'mapromocode' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wapi/restaurant/mapromocode',
                    'defaults' => array(
                        'controller' => 'Restaurant\Controller\WebMaRestaurantPromocode'
                    )
                )
            ),
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Restaurant\Controller\Overview' => 'Restaurant\Controller\OverviewController',
            'Restaurant\Controller\Story' => 'Restaurant\Controller\StoryController',
            'Restaurant\Controller\Detail' => 'Restaurant\Controller\DetailController',
            'Restaurant\Controller\Review' => 'Restaurant\Controller\ReviewController',
            'Restaurant\Controller\Menu' => 'Restaurant\Controller\MenuController',
            'Restaurant\Controller\Gallery' => 'Restaurant\Controller\GalleryController',
            'Restaurant\Controller\MenuAddons' => 'Restaurant\Controller\MenuAddonsController',
            'Restaurant\Controller\Config' => 'Restaurant\Controller\ConfigController',
            'Restaurant\Controller\PreOrder' => 'Restaurant\Controller\PreOrderController',
            'Restaurant\Controller\DistanceCheck' => 'Restaurant\Controller\DistanceCheckController',
            'Restaurant\Controller\Config' => 'Restaurant\Controller\ConfigController',
            'Restaurant\Controller\Timeslots' => 'Restaurant\Controller\TimeslotsController',
            'Restaurant\Controller\CurrentTime' => 'Restaurant\Controller\CurrentTimeController',
            'Restaurant\Controller\ClosedDate' => 'Restaurant\Controller\ClosedDateController',
            'Restaurant\Controller\DealsCoupons' => 'Restaurant\Controller\DealsCouponsController',
            'Restaurant\Controller\TopMenu' => 'Restaurant\Controller\TopMenuController',
            'Restaurant\Controller\ReviewDetail' => 'Restaurant\Controller\ReviewDetailController',
            'Restaurant\Controller\RestaurantLocation' => 'Restaurant\Controller\RestaurantLocationController',
            'Restaurant\Controller\RestaurantTimeSlot' => 'Restaurant\Controller\RestaurantTimeSlotController',
            'Restaurant\Controller\RestaurantShortAddress' => 'Restaurant\Controller\RestaurantShortAddressController',
            'Restaurant\Controller\RestaurantDeleverToAddress' => 'Restaurant\Controller\RestaurantDeleverToAddressController',
            'Restaurant\Controller\ReservationTimeslot' => 'Restaurant\Controller\ReservationTimeslotController',
            'Restaurant\Controller\ReservationCategoryTimeslots' => 'Restaurant\Controller\ReservationCategoryTimeslotsController',
            'Restaurant\Controller\WebTopMenu' => 'Restaurant\Controller\WebTopMenuController',
            'Restaurant\Controller\WebShortReview' => 'Restaurant\Controller\WebShortReviewController',
            'Restaurant\Controller\WebStory' => 'Restaurant\Controller\WebStoryController',
            'Restaurant\Controller\WebGallery' => 'Restaurant\Controller\WebGalleryController',
            'Restaurant\Controller\WebRestaurantGeneralDetails' => 'Restaurant\Controller\WebRestaurantGeneralDetailsController',
            'Restaurant\Controller\WebOverview' => 'Restaurant\Controller\WebOverviewController',
            'Restaurant\Controller\WebReview' => 'Restaurant\Controller\WebReviewController',
            'Restaurant\Controller\WebMenu' => 'Restaurant\Controller\WebMenuController',
            'Restaurant\Controller\WebReportAbuse' => 'Restaurant\Controller\WebReportAbuseController',
            'Restaurant\Controller\WebAddons' => 'Restaurant\Controller\WebAddonsController',
            'Restaurant\Controller\WebOrder' => 'Restaurant\Controller\WebOrderController',
            //'Restaurant\Controller\WebOrder' =>'User\Controller\OrderPlaceController',//order testing for mobile app
            'Restaurant\Controller\WebDealsCoupons' => 'Restaurant\Controller\WebDealsCouponsController',
            'Restaurant\Controller\WebUserFeedback' => 'Restaurant\Controller\WebUserFeedbackController',
            'Restaurant\Controller\WebRestaurantTimeSlot' => 'Restaurant\Controller\WebRestaurantTimeSlotController',
            'Restaurant\Controller\WebReservation' => 'Restaurant\Controller\WebReservationController',
            'Restaurant\Controller\WebReservationInvite' => 'Restaurant\Controller\WebReservationInviteController',
            'Restaurant\Controller\WebOwnersResponse' => 'Restaurant\Controller\WebOwnersResponseController',
            'Restaurant\Controller\WebOwnersLogin' => 'Restaurant\Controller\WebOwnersLoginController',
            'Restaurant\Controller\WebFriendActionOnRestaurant' => 'Restaurant\Controller\WebFriendActionOnRestaurantController',
            'Restaurant\Controller\WebMenuDeals' => 'Restaurant\Controller\WebMenuDealsController',
            'Restaurant\Controller\WebOperatingHours' => 'Restaurant\Controller\WebOperatingHoursController',
            'Restaurant\Controller\WebRestaurantTimings' => 'Restaurant\Controller\WebRestaurantTimingsController',
            'Restaurant\Controller\WebDistanceCalculation' => 'Restaurant\Controller\WebDistanceCalculationController',
            'Restaurant\Controller\WebPointPastReservation' => 'Restaurant\Controller\WebPointPastReservationController',
            'Restaurant\Controller\WebReservationEditTimeSlot' => 'Restaurant\Controller\WebReservationEditTimeSlotController',
            'Restaurant\Controller\WebRestaurantSeo' => 'Restaurant\Controller\WebRestaurantSeoController',
            'Restaurant\Controller\WebRestaurantDomain' => 'Restaurant\Controller\WebRestaurantDomainController',
            'Restaurant\Controller\WebDeliveryPrice' => 'Restaurant\Controller\WebDeliveryPriceController',
            'Restaurant\Controller\WebServiceProvider' => 'Restaurant\Controller\WebServiceProviderController',
            'Restaurant\Controller\WebUserPromocodes' => 'Restaurant\Controller\WebUserPromocodesController',
            'Restaurant\Controller\UserFeedback'=>'Restaurant\Controller\UserFeedbackController',
            'Restaurant\Controller\OperatingHours'=>'Restaurant\Controller\OperatingHoursController',
            'Restaurant\Controller\WebOperatingDayDate'=>'Restaurant\Controller\WebOperatingDayDateController',
            'Restaurant\Controller\UserPromocodes'=>'Restaurant\Controller\UserPromocodesController',
            'Restaurant\Controller\SocialProofing'=>'Restaurant\Controller\SocialProofingController',
            'Restaurant\Controller\MenuSocialProofing'=>'Restaurant\Controller\MenuSocialProofingController',
            'Restaurant\Controller\WebDeliveryCheck'=>'Restaurant\Controller\WebDeliveryCheckController',
            'Restaurant\Controller\DeliveryCheck'=>'Restaurant\Controller\DeliveryCheckController',
            'Restaurant\Controller\WebFeaturedRestaurant'=>'Restaurant\Controller\WebFeaturedRestaurantController',
            'Restaurant\Controller\PointPastReservation'=>'Restaurant\Controller\PointPastReservationController',
            'Restaurant\Controller\WebSocialMediaActivity' => 'Restaurant\Controller\WebSocialMediaActivityController',
            'Restaurant\Controller\WebSweepstakesRestaurant' => 'Restaurant\Controller\WebSweepstakesRestaurantController',
            'Restaurant\Controller\WebRestaurantNameLoyalityCode'=>'Restaurant\Controller\WebRestaurantNameLoyalityCodeController',
            'Restaurant\Controller\RestaurantNameLoyalityCode'=>'Restaurant\Controller\RestaurantNameLoyalityCodeController',
            'Restaurant\Controller\WebMenuSpecificDeal' => 'Restaurant\Controller\WebMenuSpecificDealController',
            'Restaurant\Controller\DinenmoreDeal' => 'Restaurant\Controller\DinenmoreDealController',
            'Restaurant\Controller\MenuSpecificDeal' => 'Restaurant\Controller\MenuSpecificDealController',
            'Restaurant\Controller\MenuNew' => 'Restaurant\Controller\MenuNewController',
            'Restaurant\Controller\DinenmoreRestaurantsDetails' => 'Restaurant\Controller\DinenmoreRestaurantsDetailsController',
            'Restaurant\Controller\RestaurantDeals' => 'Restaurant\Controller\RestaurantDealsController',
            'Restaurant\Controller\CuratedList'=>'Restaurant\Controller\CuratedListController',
            'Restaurant\Controller\WebMaRestaurantPromocode'=>'Restaurant\Controller\WebMaRestaurantPromocodeController',
        )
    ),
    'constants' => array(
        'campaigns' => array(
            'sweepstakes'=>false,
            'promotion_five_dollar'=>false,            
        ),
    )
);
