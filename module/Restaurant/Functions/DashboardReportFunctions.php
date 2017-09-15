<?php

namespace Restaurant;

use Restaurant\Model\Restaurant;
use Restaurant\Model\Tags;
use User\Model\UserOrder;
use User\Model\UserReservation;
use MCommons\StaticOptions;
use MCommons\CommonFunctions;
use Zend\Json\Json;

class DashboardReportFunctions {

    public static $config = array(
        'adapter' => 'Zend\Http\Client\Adapter\Curl',
        'curloptions' => array(
            CURLOPT_FOLLOWLOCATION => true
        )
    );

    public static function getRestaurantsIdsForReports() {
        $restaurants = new Restaurant();
        $tags = new Tags();
        $tagDetails = $tags->getTagDetailByName('dine-more');
        if (!empty($tagDetails)) {
            $tagId = $tagDetails[0]['tags_id'];
        }
        $restaurantData = $restaurants->getDineAndMoreTaggedRestaurants($tagId);
        return $restaurantData;
    }

    public static function getFacebookData($restId) {
        try {
            $sl = StaticOptions::getServiceLocator();
            $config = $sl->get('Config');
            $accessToken = $config['constants']['facebook']['access_token'];
            $restaurantModal = new \Restaurant\Model\RestaurantSocialMediaActivity();
            $socialUrls = $restaurantModal->getResSocialUrls($restId);
            if (!empty($socialUrls)) {
                $fbUrl = trim(isset($socialUrls['fb_like_url']) && $socialUrls['fb_like_url'] != '' ? $socialUrls['fb_like_url'] : "");
                $data = [];
                if ($fbUrl != '') {
                    $data['restaurant_id'] = $restId;
                    $data['fburl'] = $fbUrl;
                    $fbSourceUrl = $config ['facebook'] ['facebook_url'] . "?id=" . $fbUrl . "&access_token=" . $accessToken . "&fields=checkins,likes,were_here_count,rating_count,overall_star_rating";
                    $fbResponse = CommonFunctions::curlRequest($fbSourceUrl, 'GET');
                    if ($fbResponse != '') {
                        $data['comments'] = 0;
                        $data['followers'] = isset($fbResponse['likes']) ? $fbResponse['likes'] : 0;
                        $data['rating'] = isset($fbResponse['overall_star_rating']) ? $fbResponse['overall_star_rating'] : 0;
                        $data['reviews'] = isset($fbResponse['rating_count']) ? $fbResponse['rating_count'] : 0;
                        $data['likes'] = isset($fbResponse['likes']) ? $fbResponse['likes'] : 0;
                        $data['checkins'] = isset($fbResponse['were_here_count']) ? $fbResponse['were_here_count'] : 0;
                        $data['page_id'] = isset($fbResponse['id']) ? $fbResponse['id'] : '';
                    } else {
                        $data['comments'] = 0;
                        $data['followers'] = 0;
                        $data['rating'] = 0;
                        $data['reviews'] = 0;
                        $data['likes'] = 0;
                        $data['checkins'] = 0;
                        $data['page_id'] = '';
                    }
                } else {
                    $data['comments'] = 0;
                    $data['followers'] = 0;
                    $data['rating'] = 0;
                    $data['reviews'] = 0;
                    $data['likes'] = 0;
                    $data['checkins'] = 0;
                    $data['page_id'] = '';
                }
                if ($data['page_id'] != '') {
                    $uri = $config ['facebook'] ['facebook_url'] . $data['page_id'] . "/feed?access_token=" . $accessToken;
                    $response = CommonFunctions::curlRequest($uri, 'GET');
                    $fbData = self::extractFeedDataForFacebook($response, $accessToken);
                    if ($fbData) {
                        $data['comments'] = 0;
                        $data['messages'] = $fbData['instaData']['messages'];
                        $data['most_commented'] = 0; //$fbData['instaData']['most_commented'];
                        $data['most_liked'] = $fbData['instaData']['most_liked'];
                        $data['most_popular_content'] = $fbData['most_popular_content'];
                    } else {
                        $data['comments'] = 0;
                        $data['messages'] = 0;
                        $data['most_commented'] = 0;
                        $data['most_liked'] = 0;
                        $data['most_popular_content'] = [];
                    }
                }
                $data['comments'] = $data['reviews'] + $data['comments'];
            } else {
                $data['likes'] = 0;
                $data['checkins'] = 0;
                $data['page_id'] = '';
                $data['followers'] = 0;
                $data['reviews'] = 0;
                $data['rating'] = 0;
                $data['messages'] = 0;
                $data['comments'] = 0;
                $data['most_commented'] = 0;
                $data['most_liked'] = 0;
                $data['most_popular_content'] = [];
            }
            return $data;
        } catch (\Exception $e) {
            $data = [];
            $data['restaurant_id'] = $restId;
            $data['likes'] = 0;
            $data['checkins'] = 0;
            $data['page_id'] = '';
            $data['followers'] = 0;
            $data['reviews'] = 0;
            $data['rating'] = 0;
            $data['messages'] = 0;
            $data['comments'] = 0;
            $data['most_commented'] = 0;
            $data['most_liked'] = 0;
            $data['most_popular_content'] = [];
            \MUtility\MunchLogger::writeLog($e, 1, 'Something Went Wrong On Facebook Api');
            //throw new \Exception($e->getMessage(), 400);
            return $data;
        }
    }

    public static function getTwitterData($restId) {
        try {
            $sl = StaticOptions::getServiceLocator();
            $config = $sl->get('Config');
            $notweets = 10;
            $twitterData = [];
            $twitterData['followers'] = 0;
            $twitterData['following'] = 0;
            $twitterData['feeds_count'] = 0;
            $restModal = new Restaurant();
            $socialUrls = $restModal->getRestaurantSocialUrls($restId);
            if (!empty($socialUrls)) {
                $twitterUrl = trim(isset($socialUrls['twitter_url']) && $socialUrls['twitter_url'] != '' ? $socialUrls['twitter_url'] : "");
            }
            $screenName = str_replace('https://twitter.com/', '', $twitterUrl);
            if (!empty($screenName)) {
                $connection = StaticOptions::getConnectionWithTwitterAccessToken($config ['constants']['twitter']['key'], $config ['constants']['twitter']['secret'], $accesstoken = "", $accesstokensecret = "");
                $data = $connection->get($config ['twitter'] ['twitterfeed_url'] . "?screen_name=" . $screenName . "&count=" . $notweets);
                if (!empty($data)) {
                    foreach ($data as $key => $val) {
                        if ($val->user) {
                            $twitterData['followers'] = ($val->user->followers_count) ? $val->user->followers_count : 0;
                            $twitterData['following'] = ($val->user->following) ? $val->user->following : 0;
                            $tweetsData = self::extractFeedDataForTwitter($data);
                        }
                    }
                }
            }
            if (empty($tweetsData)) {
                $twitterData['twitter_feeds'] = array();
            } else {
                $twitterData['feeds_count'] = count($tweetsData);
                $twitterData['twitter_feeds'] = $tweetsData;
            }
            return $twitterData;
        } catch (\Exception $e) {
            $twitterData = [];
            $twitterData['followers'] = 0;
            $twitterData['following'] = 0;
            $twitterData['feeds_count'] = 0;
            $twitterData['twitter_feeds'] = array();
            \MUtility\MunchLogger::writeLog($e, 1, 'Something Went Wrong On Facebook Api');
            //throw new \Exception($e->getMessage(), 400);
            return $twitterData;
        }
    }

    public static function getFourSquareData($restId) {
        try {
            $restaurantModal = new \Restaurant\Model\RestaurantSocialMediaActivity();
            $socialUrls = $restaurantModal->getResSocialUrls($restId);
            if (!empty($socialUrls)) {
                $url = trim(isset($socialUrls['foursquare_rating_url']) && $socialUrls['foursquare_rating_url'] != '' ? $socialUrls['foursquare_rating_url'] : "");
                $data = [];
                if ($url != '') {
                    $fourSquUrlCode = substr(strrchr($url, '/'), 1);
                    $fourSourceUrl = "https://api.foursquare.com/v2/venues/$fourSquUrlCode?oauth_token=ECC0YK4X0DBALF20ZCW5A1FJOVCNJ14XVAATJQPDVIEKI03R&v=20160505";
                    $fourResponse = CommonFunctions::curlRequest($fourSourceUrl, 'GET');
                    if ($fourResponse != '') {
                        $data['rating'] = isset($fourResponse['response']['venue']['rating']) ? $fourResponse['response']['venue']['rating'] : 0;
                    } else {
                        $data['rating'] = 0;
                    }
                } else {
                    $data['rating'] = 0;
                }
            } else {
                $data['rating'] = 0;
            }
            return $data;
        } catch (\Exception $e) {
            $data = [];
            $data['rating'] = 0;
            \MUtility\MunchLogger::writeLog($e, 1, 'Something Went Wrong On Foursqure Api');
            //throw new \Exception($e->getMessage(), 400);
            return $data;
        }
    }

    public static function getInstagramData($restId) {
        try {
            $sl = StaticOptions::getServiceLocator();
            $config = $sl->get('Config');
            $instagramData = [];
            $restModal = new Restaurant();
            $socialUrls = $restModal->getRestaurantSocialUrls($restId);
            if (!empty($socialUrls)) {
                $instagramUrl = trim(isset($socialUrls['instagram_url']) && $socialUrls['instagram_url'] != '' ? $socialUrls['instagram_url'] : "");
            }
            if (!empty($instagramUrl)) {
                $instaUsername = explode('/', $instagramUrl);
                $feedUrl = 'https://www.instagram.com/' . $instaUsername[3] . '/media/';
                $raw = file_get_contents('https://www.instagram.com/' . $instaUsername[3]);
                preg_match('/\"followed_by\"\:\s?\{\"count\"\:\s?([0-9]+)/', $raw, $response);
                $result = self::fetch_data($feedUrl);
                $feedResponse = json_decode($result);
                $mediaResponce = self::extractFeedDataForInstagram($feedResponse);
                if ($response) {
                    $instagramData['following'] = 0;
                    $instagramData['followers'] = $response[1];
                    $instagramData['comments'] = $mediaResponce['instaData']['comments'];
                    $instagramData['most_commented'] = $mediaResponce['instaData']['most_commented'];
                    $instagramData['most_liked'] = $mediaResponce['instaData']['most_liked'];
                    $instagramData['most_popular_content'] = $mediaResponce['most_popular_content'];
                }
            } else {
                $instagramData['followers'] = 0;
                $instagramData['following'] = 0;
                $instagramData['comments'] = 0;
                $instagramData['most_commented'] = 0;
                $instagramData['most_liked'] = 0;
                $instagramData['most_popular_content'] = [];
            }
            return $instagramData;
        } catch (\Exception $e) {
            $instagramData = [];
            $instagramData['followers'] = 0;
            $instagramData['following'] = 0;
            $instagramData['comments'] = 0;
            $instagramData['most_commented'] = 0;
            $instagramData['most_liked'] = 0;
            $instagramData['most_popular_content'] = [];
            \MUtility\MunchLogger::writeLog($e, 1, 'Something Went Wrong On Facebook Api');
            //throw new \Exception($e->getMessage(), 400);
            return $instagramData;
        }
    }

    public static function getAnalyticsData($restId, $restName, $startDate, $endDate, $restStartDate, $restEndDate) {
        $restStartDate = substr($restStartDate, 0, 10);
        $restEndDate = substr($restEndDate, 0, 10);
        $startDate = substr($startDate, 0, 10);
        $endDate = substr($endDate, 0, 10);
        try {
            $sl = StaticOptions::getServiceLocator();
            $config = $sl->get('Config');
            $analytics = [];
            $client = new \Google_Client();
            $client->setApplicationName('demomunch');
            $object = new \Google_Service_Analytics($client);
            $scopes = array(\Google_Service_Analytics::ANALYTICS_READONLY);
            $client->setScopes($scopes);
            //$authFile = '/home/manoj/workspace/munch/vendor/testing-6f176830ea0f.json';
            $authFile = $config['ga']['authfile'];
            $client->setAuthConfigFile($authFile);
            if ($client->isAccessTokenExpired()) {
                $client->refreshTokenWithAssertion($client->setAuthConfigFile($authFile));
            }
            $projectId = $config['ga']['google_analytics_projectid'];
            //$restname = "basera-indian-bistro";
            //$restId = "58285";
            $currentGoogleresults1 = $object->data_ga->get(
                    'ga:' . $projectId, $restStartDate, $restEndDate, 'ga:sessions,ga:users,ga:pageviews,ga:avgSessionDuration,ga:transactions,ga:avgTimeOnPage,ga:percentNewSessions,ga:newUsers', array(
                'segment' => 'sessions::condition::ga:pagePath=~/restaurants/' . $restName . '/' . $restId . '/'
            ));
            $currentGoogleresultsGallery = $object->data_ga->get(
                    'ga:' . $projectId, $restStartDate, $restEndDate, 'ga:pageviews', array(
                'segment' => 'sessions::condition::ga:pagePath=~/restaurants/' . $restName . '/' . $restId . '/gallery'
            ));
            $currentGoogleresultsMenu = $object->data_ga->get(
                    'ga:' . $projectId, $restStartDate, $restEndDate, 'ga:pageviews', array(
                'segment' => 'sessions::condition::ga:pagePath=~/restaurants/' . $restName . '/' . $restId . '/menu'
            ));
            $currentGoogleresultsReviews = $object->data_ga->get(
                    'ga:' . $projectId, $restStartDate, $restEndDate, 'ga:pageviews', array(
                'segment' => 'sessions::condition::ga:pagePath=~/restaurants/' . $restName . '/' . $restId . '/reviews'
            ));
            $currentGoogleresultsDineMore = $object->data_ga->get(
                    'ga:' . $projectId, $restStartDate, $restEndDate, 'ga:pageviews', array(
                'segment' => 'sessions::condition::ga:pagePath=~/restaurants/' . $restName . '/' . $restId . '/dine-more'
            ));
            $currentGoogleresultsCheckout = $object->data_ga->get(
                    'ga:' . $projectId, $restStartDate, $restEndDate, 'ga:pageviews', array(
                'segment' => 'sessions::condition::ga:pagePath=~/restaurants/' . $restName . '/' . $restId . '/order-checkout'
            ));
            $currentGoogleresultsSocial = $object->data_ga->get(
                    'ga:' . $projectId, $restStartDate, $restEndDate, 'ga:sessions', array(
                'segment' => 'sessions::condition::ga:pagePath=~/restaurants/' . $restName . '/' . $restId . '/;condition::ga:channelGrouping=@Social'
            ));
            $currentGoogleresultsDesktop = $object->data_ga->get(
                    'ga:' . $projectId, $restStartDate, $restEndDate, 'ga:sessions', array(
                'segment' => 'sessions::condition::ga:deviceCategory=@desktop;condition::ga:pagePath=~/restaurants/' . $restName . '/' . $restId . '/'
            ));
            $currentGoogleresultsMobile = $object->data_ga->get(
                    'ga:' . $projectId, $restStartDate, $restEndDate, 'ga:sessions', array(
                'segment' => 'sessions::condition::ga:isMobile==Yes;condition::ga:pagePath=~/restaurants/' . $restName . '/' . $restId . '/'
            ));
            $currentGoogleresultsDisplay = $object->data_ga->get(
                    'ga:' . $projectId, $restStartDate, $restEndDate, 'ga:sessions', array(
                'segment' => 'sessions::condition::ga:pagePath=~/restaurants/' . $restName . '/' . $restId . '/;condition::ga:medium=@Display',
            ));
            $currentGoogleresultsSearch = $object->data_ga->get(
                    'ga:' . $projectId, $restStartDate, $restEndDate, 'ga:sessions', array(
                'segment' => 'sessions::condition::ga:pagePath=~/restaurants/' . $restName . '/' . $restId . '/;condition::ga:channelGrouping=@Organic Search',
            ));
            $currentGoogleresultsDirect = $object->data_ga->get(
                    'ga:' . $projectId, $restStartDate, $restEndDate, 'ga:sessions', array(
                'segment' => 'sessions::condition::ga:pagePath=~/restaurants/' . $restName . '/' . $restId . '/;condition::ga:channelGrouping=@Direct',
            ));
            $currentGoogleresultsEmail = $object->data_ga->get(
                    'ga:' . $projectId, $restStartDate, $restEndDate, 'ga:sessions', array(
                'segment' => 'sessions::condition::ga:pagePath=~/restaurants/' . $restName . '/' . $restId . '/;condition::ga:channelGrouping=@Email',
            ));
            $currentGoogleresultsReferral = $object->data_ga->get(
                    'ga:' . $projectId, $restStartDate, $restEndDate, 'ga:sessions', array(
                'segment' => 'sessions::condition::ga:pagePath=~/restaurants/' . $restName . '/' . $restId . '/;condition::ga:channelGrouping=@Referral',
            ));
            $currentGoogleresultsDesktopTraffic = $object->data_ga->get(
                    'ga:' . $projectId, $startDate, $endDate, 'ga:sessions', array(
                'segment' => 'sessions::condition::ga:deviceCategory=@desktop;condition::ga:pagePath=~/restaurants/' . $restName . '/' . $restId . '/'
            ));
            $currentGoogleresultsMobileTraffic = $object->data_ga->get(
                    'ga:' . $projectId, $startDate, $endDate, 'ga:sessions', array(
                'segment' => 'sessions::condition::ga:isMobile==Yes;condition::ga:pagePath=~/restaurants/' . $restName . '/' . $restId . '/'
            ));
            if (!empty($currentGoogleresults1->getRows())) {
                $rows = current($currentGoogleresults1->getRows());
            } else {
                $rows[0] = 0;
                $rows[1] = 0;
                $rows[2] = 0;
                $rows[3] = 0;
                $rows[4] = 0;
                $rows[5] = 0;
            }
            if (!empty($currentGoogleresultsSocial)) {
                $socialData = current($currentGoogleresultsSocial->getRows());
            } else {
                $socialData[0] = 0;
            }
            if (!empty($currentGoogleresultsDesktop->getRows())) {
                $webData = current($currentGoogleresultsDesktop->getRows());
            } else {
                $webData[0] = 0;
            }
            if (!empty($currentGoogleresultsMobile->getRows())) {
                $mobData = current($currentGoogleresultsMobile->getRows());
            } else {
                $mobData[0] = 0;
            }
            if (!empty($currentGoogleresultsDisplay->getRows())) {
                $displayData = current($currentGoogleresultsDisplay->getRows());
            } else {
                $displayData[0] = 0;
            }
            if (!empty($currentGoogleresultsSearch->getRows())) {
                $searchData = current($currentGoogleresultsSearch->getRows());
            } else {
                $searchData[0] = 0;
            }
            if (!empty($currentGoogleresultsDirect->getRows())) {
                $directData = current($currentGoogleresultsDirect->getRows());
            } else {
                $directData[0] = 0;
            }
            if (!empty($currentGoogleresultsEmail->getRows())) {
                $emailData = current($currentGoogleresultsEmail->getRows());
            } else {
                $emailData[0] = 0;
            }
            if (!empty($currentGoogleresultsReferral->getRows())) {
                $referralData = current($currentGoogleresultsReferral->getRows());
            } else {
                $referralData[0] = 0;
            }
            if (!empty($currentGoogleresultsDesktopTraffic->getRows())) {
                $webTraffic = current($currentGoogleresultsDesktopTraffic->getRows());
            } else {
                $webTraffic[0] = 0;
            }
            if (!empty($currentGoogleresultsMobileTraffic->getRows())) {
                $mobTraffic = current($currentGoogleresultsMobileTraffic->getRows());
            } else {
                $mobTraffic[0] = 0;
            }
            if (!empty($currentGoogleresultsGallery->getRows())) {
                $gallery = current($currentGoogleresultsGallery->getRows());
            } else {
                $gallery[0] = 0;
            }
            if (!empty($currentGoogleresultsMenu->getRows())) {
                $menu = current($currentGoogleresultsMenu->getRows());
            } else {
                $menu[0] = 0;
            }
            if (!empty($currentGoogleresultsReviews->getRows())) {
                $reviews = current($currentGoogleresultsReviews->getRows());
            } else {
                $reviews[0] = 0;
            }
            if (!empty($currentGoogleresultsDineMore->getRows())) {
                $dinemore = current($currentGoogleresultsDineMore->getRows());
            } else {
                $dinemore[0] = 0;
            }
            if (!empty($currentGoogleresultsCheckout->getRows())) {
                $checkout = current($currentGoogleresultsCheckout->getRows());
            } else {
                $checkout[0] = 0;
            }
            $analytics['restaurant_id'] = (int) $restId;
            $analytics['visit'] = $rows[0];
            $analytics['page_per_visit'] = ($rows[2] == 0) ? 0 : $rows[2] / $rows[0];
            $analytics['users'] = $rows[1];
            $analytics['avg_time_profile'] = $rows[3];
            $analytics['new_customer'] = $rows[7];
            $analytics['returing_customer'] = $rows[1] - $rows[7];
            $analytics['page_views'] = $rows[2];
            $analytics['tranjactions'] = $rows[4];
            $analytics['total_traffic'] = $rows[0];
            $analytics['web_traffic'] = $webData[0];
            $analytics['mob_traffic'] = $mobData[0];
            $analytics['display_add'] = $displayData[0];
            $analytics['search_add'] = $searchData[0];
            $analytics['social_media'] = $socialData[0];
            $analytics['direct'] = $directData[0];
            $analytics['emails'] = $emailData[0];
            $analytics['referral'] = $referralData[0];
            $analytics['others'] = $rows[0] - ($directData[0] + $emailData[0] + $displayData[0] + $searchData[0] + $socialData[0] + $referralData[0]);
            $analytics['overview_page'] = '';
            $analytics['menu_page'] = $menu[0];
            $analytics['gallery_page'] = $gallery[0];
            $analytics['review_page'] = $reviews[0];
            $analytics['dine_more'] = $dinemore[0];
            $analytics['checkout_page'] = $checkout[0];
            $analytics['other_page'] = $rows[0] - ($menu[0] + $gallery[0] + $reviews[0] + $dinemore[0] + $checkout[0]);
            $analytics['traffic_array'][] = ['date' => $startDate, 'website' => $webTraffic[0], 'mobile' => $mobTraffic[0]];
            return $analytics;
        } catch (\Exception $e) {
            \MUtility\MunchLogger::writeLog($e, 1, 'Something Went Wrong On Analytics Api');
            //throw new \Exception($e->getMessage(), 400);
            $analytics['restaurant_id'] = (int) $restId;
            $analytics['visit'] = 0;
            $analytics['page_per_visit'] = 0;
            $analytics['users'] = 0;
            $analytics['avg_time_profile'] = 0;
            $analytics['new_customer'] = 0;
            $analytics['returing_customer'] = 0;
            $analytics['page_views'] = 0;
            $analytics['tranjactions'] = 0;
            $analytics['total_traffic'] = 0;
            $analytics['web_traffic'] = 0;
            $analytics['mob_traffic'] = 0;
            $analytics['display_add'] = 0;
            $analytics['search_add'] = 0;
            $analytics['social_media'] = 0;
            $analytics['direct'] = 0;
            $analytics['emails'] = 0;
            $analytics['referral'] = 0;
            $analytics['others'] = 0;
            $analytics['overview_page'] = 0;
            $analytics['menu_page'] = 0;
            $analytics['gallery_page'] = 0;
            $analytics['review_page'] = 0;
            $analytics['dine_more'] = 0;
            $analytics['checkout_page'] = 0;
            $analytics['other_page'] = 0;
            $analytics['traffic_array'][] = ['date' => $startDate, 'website' => [], 'mobile' => []];
            return $analytics;
        }
    }

    public static function getEmailsData($restId, $startDate, $endDate, $restStartDate, $restEndDate) {
        try {
            $object = new Model\EmailSent();
            $emails = $object->getEmailDetails($restId, $restStartDate, $restEndDate);
            $emailsData = [];
            if (!empty($emails)) {
                $emailsData['total_mails_sent'] = $emails['total_sent'];
                $emailsData['total_mails_opened'] = $emails['total_opened'];
                $emailsData['total_mails_clicked'] = $emails['total_clicked'];
                $emailsData['message_sent'] = 0;
            } else {
                $emailsData['total_mails_sent'] = 0;
                $emailsData['total_mails_opened'] = 0;
                $emailsData['total_mails_clicked'] = 0;
                $emailsData['message_sent'] = 0;
            }
            return $emailsData;
        } catch (\Exception $e) {
            \MUtility\MunchLogger::writeLog($e, 1, 'Something Went Wrong On Emails Api');
            //throw new \Exception($e->getMessage(), 400);
            $emailsData['total_mails_sent'] = 0;
            $emailsData['total_mails_opened'] = 0;
            $emailsData['total_mails_clicked'] = 0;
            $emailsData['message_sent'] = 0;
        }
    }

    public static function getAbandonedCartData($restId) {
        try {
            $object = new \Search\Model\AbandonedCart();
            $cartData = $object->getAbandonedCart($restId);
            $cartData['total'] = count($cartData);
            return $cartData;
        } catch (\Exception $e) {
            $cartData = [];
            $cartData['total'] = 0;
            \MUtility\MunchLogger::writeLog($e, 1, 'Something Went Wrong On Abandoment Cart Data');
            // throw new \Exception($e->getMessage(), 400);
            return $cartData;
        }
    }

    public static function getOrdersData($restId, $startDate, $endDate, $restStartDate, $restEndDate) {
        try {
            $object = new UserOrder();
            $orderDetailObject = new \User\Model\UserOrderDetail();
            $orderIds = $object->getRestaurantOrderIds($restId, $restStartDate, $restEndDate);
            $totalItems = $orderDetailObject->getRestaurantTotalOrderItems($orderIds);
            $ordersData = $object->getRestaurantTotalOrdersAndRevenu($restId, $restStartDate, $restEndDate);
            $orderType = $object->getRestaurantTakeoutVSDelivery($restId, $restStartDate, $restEndDate);
            $customerType = $object->getRestaurantNewVSReturningCustomers($restId, $restStartDate, $restEndDate);
            $popularItems = $orderDetailObject->getRestaurantMostPopularItems($restId, $restStartDate, $restEndDate);
            $Orders['order_alltime']['total_orders'] = $ordersData['total_orders'];
            $Orders['order_alltime']['total_revenue'] = $ordersData['total_revenue'];
            $Orders['order_alltime']['success_orders'] = $object->getRestaurantTotalSuccessOrders($restId, $restStartDate, $restEndDate);
            $Orders['order_alltime']['takeout'] = $orderType['takeout'];
            $Orders['order_alltime']['delivery'] = $orderType['delivery'];
            $Orders['order_alltime']['avg_item'] = ($ordersData['total_orders'] == 0) ? 0 : $totalItems / $ordersData['total_orders'];
            $Orders['order_alltime']['total_customers'] = $customerType['total_customers'];
            $Orders['order_alltime']['new_customers'] = $customerType['new_customers'];
            $Orders['order_alltime']['returning_customers'] = $customerType['returning_customers'];
            $Orders['order_data'] = ($object->getRestaurantOrders($restId, $startDate, $endDate) ? $object->getRestaurantOrders($restId, $startDate, $endDate) : []);
            $Orders['most_popular_items'] = ($popularItems ? $popularItems : []);
            return $Orders;
        } catch (\Exception $e) {
            \MUtility\MunchLogger::writeLog($e, 1, 'Something Went Wrong On Order Data');
            //throw new \Exception($e->getMessage(), 400);
        }
    }

    public static function getReservationsData($restId, $startDate, $endDate, $restStartDate, $restEndDate) {
        try {
            $object = new UserReservation();
            $server = new Model\RestaurantServer();
            $memberIds = $server->getMembersIds($restId, $restStartDate, $restEndDate);
            $customerType = $object->getRestaurantNewVSReturningCustomers($restId, $restStartDate, $restEndDate);
            $reservations['reservation_alltime'] = $object->getRestaurantTotalReservationsAndSeats($restId, $restStartDate, $restEndDate);
            $reservations['reservation_alltime']['success_reservations'] = $object->getRestaurantTotalSuccessReservations($restId, $restStartDate, $restEndDate);
            $reservations['reservation_alltime']['total_cancellations'] = $object->getRestaurantTotalCancellations($restId, $restStartDate, $restEndDate);
            $reservations['reservation_alltime']['total_customers'] = $customerType['total_customers'];
            $reservations['reservation_alltime']['new_customers'] = $customerType['new_customers'];
            $reservations['reservation_alltime']['returning_customers'] = $customerType['returning_customers'];
            $reservations['reservation_alltime']['total_reservations_members'] = $object->getRestaurantMembersReservations($restId, $restStartDate, $restEndDate);
            $reservations['reservation_alltime']['total_reservations_normal_users'] = $object->getRestaurantNormalUserReservations($restId, $memberIds, $restStartDate, $restEndDate);
            $reservations['reservation_data'] = ($object->getRestaurantReservations($restId, $startDate, $endDate) ? $object->getRestaurantReservations($restId, $startDate, $endDate) : []);
            return $reservations;
        } catch (\Exception $e) {
            \MUtility\MunchLogger::writeLog($e, 1, 'Something Went Wrong On Reservation Data');
            //throw new \Exception($e->getMessage(), 400);
        }
    }

    public static function getDineAndMoreData($restId, $startDate, $endDate, $restStartDate, $restEndDate) {
        try {
            $object = new Model\RestaurantServer();
            $reservation = new UserReservation();
            $points = new \User\Model\UserPoints();
            $activityFeeds = new \User\Model\ActivityFeed();
            $referrals = new \User\Model\UserReferrals();
            $order = new UserOrder();
            $offers = new Model\DealsCoupons();
            $memberIds = $object->getMembersIds($restId, $restStartDate, $restEndDate);
            $pointsData = $points->getRestaurantsTotalPoints($restId, $restStartDate, $restEndDate);
            $dailyPointsData = $points->getRestaurantsTotalPointsDaily($restId, $startDate, $endDate);
            $membersData = $order->getRestaurantDineAndMoreRevenue($restId, $restStartDate, $restEndDate);
            $membersRevenueDaily = $order->getRestaurantDineAndMoreRevenueDaily($restId, $startDate, $endDate);
            $normalUsersData = $order->getRestaurantNormalUsersRevenue($restId, $restStartDate, $restEndDate);
            $dinMoreUsers = $object->getDineAndMoreCustomers($restId, $restStartDate, $restEndDate);
            $orderData = $order->getRestaurantTotalOrdersAndRevenu($restId, $restStartDate, $restEndDate);
            $mostActiveMembers = [];
            foreach ($dinMoreUsers as $key => $value) {
                $mostActiveMembers[$key]['restaurant_id'] = $value['restaurant_id'];
                $mostActiveMembers[$key]['user_id'] = $value['user_id'];
                $mostActiveMembers[$key]['username'] = $value['first_name'] . " " . $value['last_name'];
                $mostActiveMembers[$key]['server_name'] = $value['server_name'];
                $mostActiveMembers[$key]['email'] = $value['email'];
                $mostActiveMembers[$key]['total_orders'] = $order->getUserTotalOrders($value['user_id'], $restId, $restStartDate, $restEndDate);
                $mostActiveMembers[$key]['total_reservations'] = $reservation->getUserTotalReservations($value['user_id'], $restId, $restStartDate, $restEndDate);
                $mostActiveMembers[$key]['total_points'] = $points->getUserTotalPoints($value['user_id'], $restId, $restStartDate, $restEndDate);
                $mostActiveMembers[$key]['join_date'] = $value['date'];
                $mostActiveMembers[$key]['activity_date'] = $activityFeeds->getRestaurantUserActivity($value['user_id'], $restId);
            }
            $totalMembers = $object->getRestaurantTotalCustomers($restId, $restStartDate, $restEndDate);
            $activeMembers = $activityFeeds->getRestaurantActiveMembers($restId, $memberIds, $restStartDate, $restEndDate);
            $dinemore['dinemore_alltime']['total_members'] = $totalMembers;
            $dinemore['dinemore_alltime']['new_members'] = $object->getRestaurantTotalNewCustomers($restId, $startDate, $endDate);
            $dinemore['dinemore_alltime']['active_members'] = $activeMembers;
            $dinemore['dinemore_alltime']['inactive_members'] = $totalMembers - $activeMembers;
            $dinemore['dinemore_alltime']['specials_offered'] = $offers->getRestaurantsOffers($restId, $restStartDate, $restEndDate);
            $dinemore['dinemore_alltime']['specials_converted'] = $offers->getRestaurantsOffersAvailed($restId, $restStartDate, $restEndDate);
            $dinemore['dinemore_alltime']['total_referrals'] = $referrals->getRestaurantsTotalReferrals($restId, $restStartDate, $restEndDate);
            $dinemore['dinemore_alltime']['total_points_accured'] = $pointsData['total_points_accured'];
            $dinemore['dinemore_alltime']['total_points_redeemed'] = $pointsData['total_points_redeemed'];
            $dinemore['dinemore_alltime']['total_points_redeemed_amount'] = number_format(($pointsData['total_points_redeemed'] * 0.01), 2);
            $dinemore['dinemore_alltime']['total_orders'] = $orderData['total_orders'];
            $dinemore['dinemore_alltime']['total_revenue'] = $orderData['total_revenue'];
            $dinemore['dinemore_alltime']['total_orders_members'] = $membersData['total_orders'];
            $dinemore['dinemore_alltime']['total_revenue_members'] = $membersData['total_revenue'];
            $dinemore['dinemore_alltime']['total_users_orders_members'] = $membersData['total_users_orders_members'];
            $dinemore['dinemore_alltime']['total_orders_normal_users'] = $orderData['total_orders'] - $membersData['total_orders'];
            $dinemore['dinemore_alltime']['total_revenue_normal_users'] = $orderData['total_revenue'] - $membersData['total_revenue'];
            $dinemore['dinemore_alltime']['total_users_orders_normal_users'] = $orderData['total_orders'] - $membersData['total_orders'];
            $dinemore['dine_more_members'] = $mostActiveMembers;
            $dinemore['dine_more_points']['total_points_accured'] = $dailyPointsData['total_points_accured'];
            $dinemore['dine_more_points']['total_points_redeemed'] = $dailyPointsData['total_points_redeemed'];
            $dinemore['dine_more_points']['total_points_redeemed_amount'] = number_format(($dailyPointsData['total_points_redeemed'] * 0.01), 2);
            $dinemore['dine_more_points']['total_revenue_members'] = $membersRevenueDaily['total_revenue'];
            $dinemore['dine_more_points']['total_users_orders_members'] = $membersRevenueDaily['total_orders'];
            return $dinemore;
        } catch (\Exception $e) {
            \MUtility\MunchLogger::writeLog($e, 1, 'Something Went Wrong On Reviews Data');
            //throw new \Exception($e->getMessage(), 400);
        }
    }

    public static function getReviewsRatings($restId, $restStartDate, $restEndDate) {
        try {
            $object = new \User\Model\UserReview();
            $reviewRatings = $object->getRestaurantReviewsRatings($restId, $restStartDate, $restEndDate);
            $ratings = [];
            $ratings['review_alltime']['total_reviews'] = $object->getRestaurantTotalReviews($restId, $restStartDate, $restEndDate);
            $ratings['review_alltime']['positive'] = $reviewRatings['positive'];
            $ratings['review_alltime']['negative'] = $reviewRatings['negative'];
            return $ratings;
        } catch (\Exception $e) {
            \MUtility\MunchLogger::writeLog($e, 1, 'Something Went Wrong On Reviews Data');
            //throw new \Exception($e->getMessage(), 400);
        }
    }

    public static function extractFeedDataForFacebook($data, $accessToken) {
        $sl = StaticOptions::getServiceLocator();
        $config = $sl->get('Config');
        $instaData = [];
        $mostPopular = [];
        $comments = 0;
        $mostCommented = 0;
        $mostLiked = 0;
        $messages = 0;
        if (isset($data ['data']) && !empty($data ['data'])) {
            foreach ($data ['data'] as $key => $val) {
                if (isset($val ['message']) && !empty($val ['message'])) {
                    $messages++;
                    $mostPopular [$key] ['type'] = "Facebook";
                    $mostPopular [$key] ['rest_name'] = @$val ['from']['name'];
                    $mostPopular [$key] ['title'] = @$val ['message'];
                    $mostPopular [$key] ['logo'] = '';
                    $mostPopular [$key] ['date_time'] = @$val ['created_time'];
                    if ($val ['type'] == 'photo') {
                        $uri = $config ['facebook'] ['facebook_url'] . $val ['object_id'] . "?access_token=" . $accessToken;
                        $client = new \Zend\Http\Client($uri, self::$config);
                        $req = $client->getRequest();
                        $response = $client->send($req)->getBody();
                        if (empty($response)) {
                            return array();
                        }
                        $data = Json::decode($response, Json::TYPE_ARRAY);
                        $mostPopular [$key] ['image_name'] = @$data['images'][0]['source'];
                    } else {
                        $mostPopular [$key] ['image_name'] = '';
                    }
                }
                if (isset($val ['likes']) && !empty($val ['likes'])) {
                    if (@count($val ['likes']['data']) > $mostLiked) {
                        $mostLiked = @count($val ['likes']['data']);
                        $mostlike = $mostLiked;
                    } else {
                        $instaData['most_liked'] = 0;
                    }
                    $mostPopular [$key] ['likes'] = @count($val ['likes']['data']);
                } else {
                    $mostlike = $mostLiked;
                    $mostPopular [$key] ['likes'] = 0;
                }
                $instaData['most_liked'] = $mostlike;
                $mostPopular [$key] ['comments'] = 0;
            }
            $mostPopular = self::arraySortByKey($mostPopular);
            $instaData['messages'] = $messages;
            return ['instaData' => $instaData, 'most_popular_content' => $mostPopular];
        } else {
            $instaData['comments'] = $comments;
            $instaData['most_commented'] = $mostCommented;
            $instaData['most_liked'] = $mostLiked;
            return ['instaData' => $instaData, 'most_popular_content' => $mostPopular];
        }
    }

    public static function extractFeedDataForTwitter($data) {
        $sl = StaticOptions::getServiceLocator();
        $config = $sl->get('Config');
        $tweetsDataArray = array();
        foreach ($data as $key => $val) {
            $tweetsDataArray [$key] ['type'] = "twitter";
            $tweetsDataArray [$key] ['name'] = "";
            $tweetsDataArray [$key] ['description'] = $val->text;
            if (isset($val->user)) {
                $tweetsDataArray [$key] ['url'] = $config ['twitter'] ['twitter_url'] . $val->user->screen_name;
                $tweetsDataArray [$key] ['image_url'] = $val->user->profile_image_url_https;
            }
            $tweetsDataArray [$key] ['published_date_time'] = $val->created_at;
            $tweetsDataArray [$key] ['retweet_url'] = $config ['twitter'] ['retweet_url'] . "?tweet_id=" . $val->id_str;
        }
        return $tweetsDataArray;
    }

    public static function extractFeedDataForInstagram($data) {
        $instaData = [];
        $mostPopular = [];
        $comments = 0;
        $mostCommented = 0;
        $mostLiked = 0;
        if ($data->more_available == true) {
            foreach ($data->items as $key => $val) {
                $comments += $val->comments->count;
                $instaData['comments'] = $comments;
                if ($val->comments->count > $mostCommented) {
                    $mostCommented = $val->comments->count;
                    $instaData['most_commented'] = $val->comments->count;
                }
                if ($val->likes->count > $mostLiked) {
                    $mostLiked = $val->likes->count;
                    $instaData['most_liked'] = $val->likes->count;
                }
                $mostPopular[$key]['type'] = 'Instagram';
                if (isset($val->user->username)) {
                    $mostPopular[$key]['rest_name'] = $val->user->username;
                }
                if (isset($val->location->name)) {
                    $mostPopular[$key]['rest_name'] = $val->location->name;
                }
                $mostPopular[$key]['logo'] = $val->user->profile_picture;
                $mostPopular[$key]['title'] = $val->caption->text;
                $mostPopular[$key]['date_time'] = $val->caption->text;
                $mostPopular[$key]['image_name'] = $val->images->standard_resolution->url;
                $mostPopular[$key]['likes'] = $val->likes->count;
                $mostPopular[$key]['comments'] = $val->comments->count;
            }
            $mostPopular = self::arraySortByKey($mostPopular);
            return ['instaData' => $instaData, 'most_popular_content' => $mostPopular];
        } else {
            $instaData['comments'] = 0;
            $instaData['most_commented'] = 0;
            $instaData['most_liked'] = 0;
            return ['instaData' => $instaData, 'most_popular_content' => $mostPopular];
        }
    }

    public static function fetch_data($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6000);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public static function arraySortByKey($array) {
        $sort = array();
        foreach ($array as $k => $v) {
            $sort['likes'][$k] = $v['likes'];
        }
        array_multisort($sort['likes'], SORT_DESC, $array);
        return array_slice($array, 0, 2);
    }
}
