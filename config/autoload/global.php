<?php

/**
 * Works only in local environment. No effect in other environments.
 * @param mixed $obj object to be printed
 * @param bool $exit defaults to false. If true exits the app.
 * @return NULL
 */
function pr($obj, $exit = false) {
    if(APPLICATION_ENV != 'local'){
        return ;
    }
    $bt = debug_backtrace();
    $caller = array_shift($bt);
    echo "<pre>";
    echo "\n===== Called from " . $caller['file'] . " " . $caller['line'] . " =====\n\n";
    print_r($obj);
    echo "\n\n";
    if ($exit) {
        exit;
    }
}

/**
 * Works only in local environment. No effect in other environments.
 * @param mixed $obj object to be var_dump(ed)
 * @param bool $exit defaults to false. If true exits the app.
 * @return NULL
 */
function vd($obj, $exit = false) {
    if(APPLICATION_ENV != 'local'){
        return ;
    }
    $bt = debug_backtrace();
    $caller = array_shift($bt);
    echo "<pre>";
    echo "\n===== Called from " . $caller['file'] . " " . $caller['line'] . " =====\n\n";
    var_dump($obj);
    echo "\n\n";
    if ($exit) {
        exit;
    }
}

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */
return array(
    'service_manager' => array(
        'factories' => array(
            'MCommons\Db\Adapter\WriteAdapter' => 'MCommons\Db\Adapter\WriteAdapterServiceFactory',
            'MCommons\Db\Adapter\ReadAdapter' => 'MCommons\Db\Adapter\ReadAdapterServiceFactory'
        ),
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory'
        )
    ),
    'notification' => array(
        'sender' => 'notifications@munchado.com',
        'name' => 'Munch Ado'
    ),
    'pintrest' => array(
        'pintrest_url' => 'http://pinterest.com/munchado/feed.rss',
        'repin_url' => 'http://www.pinterest.com/pin/create/button/'
    ),
    'twitter' => array(
        'twitter_url' => 'http://www.twitter.com/',
        'twitterfeed_url' => 'https://api.twitter.com/1.1/statuses/user_timeline.json',
        'retweet_url' => 'https://twitter.com/intent/retweet'
    ),
    'facebook' => array(
        'facebook_url' => 'https://graph.facebook.com/',
        'facebookshare_url' => 'http://www.facebook.com/sharer.php'
    ),
    'google+' => array(
        'googleplus_url' => 'https://www.googleapis.com/plus/v1/people/',
        'googleshare_url' => 'https://plus.google.com/share'
    ),
    'blog' => array(
        'blog_url' => 'http://blog.munchado.com/feed/'
    ),
    'instagram' => array(
        'instagram_url' => 'https://api.instagram.com/v1/users'
    ),
    'resque-service' => 1, // 1 - enabled, 0 - disabled
    'clevertap_service'=>1,
    'resque-service-dashboard'=>1,
    'resque-service-salesmanago'=>0,
    'activity-log'=>0,
    's3' => array(

        'key' => 'AKIAISDHDHDHD3334EOWAQ',
        'secret' => 'Vral4B9NjB4BqnhAb/EuzCNyx/gTzjDhh6aoPILu',

        //'key' => 'sadsadsfdsfdsf',
        //'secret' => 'dsfsfsdfsdf/EuzCNyx/gTzjDhh6aoPILu',

        'acl' => 'public_read', // public_read, public_read_write, private
        'bucket_name' => 'munch_images'
    )
);
