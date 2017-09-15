<?php
return array (
    'constants' => array (
        'protocol' => 'http',
        'imagehost' => 'qa.hungrybuzz.info/assets/',
        'facebook' => array(
            'app_key' => '160675754093111',
            'app_secret' => 'aa35db6158113b205a874e68336af808',
            'page_id' => '491596394245644',
            'access_token' => '160675754093111|dm3VQyzrl9xfmUWNlZcuiaQwojw'
        ),
        'twitter' => array(
            'handle' => 'Munch_Ado',
            'key' => '7gpJTmHONpbAHkz1hxsIA',
            'secret' => 'hGJtxZtEMgVHZCNOLvCtxrUwNXWS2vYmCGjkyRvgF0'
        ),
        'yahoo' => array(
            'consumerKey' => 'dj0yJmk9TVQwbERnYk1wTjllJmQ9WVdrOWJHOU1XVzFPTnpnbWNHbzlNVEl3TmpjME1EWXkmcz1jb25zdW1lcnNlY3JldCZ4PTNm',
            'consumerSecret' => 'c87f8b093b1c28147a64efba3e564f7007a7b0fd'
        ),
        'gmail' => array(
            'client_id' => '888693714463-66eqtkgav6n0snng5m83l1vq03ajt89v.apps.googleusercontent.com',
            'client_secret' => 'uOy5wavqu8Hkg6SUJyebtx6-'
        ),
        'hotmail' => array(
            'client_id' => '000000004011FEB1',
            'client_secret' => 'ISOeGXQmlA5Gw9mqaIuCZhx1j17LprHD',
            'scope' => 'wl.basic wl.emails wl.contacts_emails',
            'redirect_uri' => 'munch-local.com/wapi/user/microsoftcontact/microsoftauthenticate'
        ),
        'instagram' => array(            
            'client_id' => '9ce363963a7745a8ba80dc3912ef06c8',
            'client_secret' => '19271ea7d29f4bb6bcd4267294c9f312',
            'access_token' => '401132820.9ce3639.b141e77825d44b23a4f97bb240716e90'
        ),
        'stripe' => array(
            'secret_key' => 'sk_test_210sbEg9qLGepDaguTfuVnRw'
        ),
        'stripe' => array(
            //Local --QA - Demo - Staging
            'secret_key' => 'sk_test_210sbEg9qLGepDaguTfuVnRw',
        //Local --QA - Demo - Staging End
        ),
        'solr' => array(
      'protocol' => 'http://',
           'host' => 'qc.munchado.in',
           'port' => 8984,
           'context' => 'solr',
            // 'host' => 'localhost',
            // 'port' => 8983,
            // 'context' => 'solr'
        ),
        'blog' => array(
            'blog_url' => 'http://blog.munchado.com/feed/'
        ),
        // QA
        'google+' => array(
            'app_id' => '106525625756971589805',
            'api_key' => 'AIzaSyCGSFlgPwDhGVrL0Ss8pF6GZTZhV0oYLM8',
            'client_id' => '888693714463-66eqtkgav6n0snng5m83l1vq03ajt89v.apps.googleusercontent.com',
            'client_secret' => 'uOy5wavqu8Hkg6SUJyebtx6-',
            'developer_key' => 'AIzaSyCGSFlgPwDhGVrL0Ss8pF6GZTZhV0oYLM8',
            'gmail_scope' => 'https://www.google.com/m8/feeds',
            'redirect_uri' => 'munch-local.com/wapi/user/googlelogin/googleauthenticate',
            'contact_redirect_uri' => 'munch-local.com/wapi/user/googlecontact/googleauthenticate'
        ),
        'pubnub' => array(
            'PUBNUB_PUBLISH_KEY' => 'pub-c-f87e9dcb-cb4e-403c-8987-cc0866d5263e',
            'PUBNUB_SUBSCRIBE_KEY' => 'sub-c-10490f34-064b-11e3-991c-02ee2ddab7fe',
            'PUBNUB_ENQUE' => 0
        ),
        'redis' => array(
            'host' => '127.0.0.1',
            'port' => 6379,
            'channel' => 'default',
            'enabled' => true
        ),
        'email' => array(
                'demo_email' => 'notifications@munchado.com',
                'default_from' => array(
                    'name' => "Munchado Support",
                    'email' => "notifications@munchado.com"
                ),
                'smtp' => array(
                    'name' => 'MunchAdo Support',
                    'host' => 'smtp.gmail.com',
                    'port' => 465,
                    'connection_class' => 'login',
                    'connection_config' => array(
                        'username' => 'MunchAdo2015@gmail.com',
                        'password' => 'MunchAdo@2015',
                        'ssl' => 'tls'
                    )
                )
            ),
          
        // local
        'web_url' => 'munchado-local.com',
        'dashboard_url' => 'dashboard-local.com',
        'memcache' => false,
    // staging
    // 'web_url'=>'http://qc.hungrybuzz.info',
    // live
    // 'web_url'=>'http://munchado.com'
    ),
    // int counterparts of string combination
    /*
     * turn off all error reporting = 0 E_ERROR | E_WARNING | E_PARSE | E_DEPRECATED = 8199 E_ERROR | E_WARNING | E_PARSE | E_NOTICE = 15
     */
    // local
    //local
    'php-settings' => array(
        'error_reporting' => 15,
    ),
    // local
    'image_base_urls' => array(
        'local-api' => 'munch-local.com',
        'local-cms' => 'http://hbcms-local.com',
        'amazon' => 'qa.hungrybuzz.info/assets/munch_images/'
    ),
    'mongo'=>array('host'=>'mongodb://127.0.0.1:27017',
            'database' => 'MunchAdo',
            'enabled' => true,
            'user' => 'root',
            'pwd' => 'root'
        ),
    'ga' => array(
            'google_analytics_projectid' => '102104572',
            'authfile' => BASE_DIR . DS .'vendor/MunchAdo-8ddb5c1a2800.json',
    ),
    'clevertap'=>array(
            'apiurl'=>'https://api.clevertap.com',
            'X-CleverTap-Account-Id'=>'TEST-944-R78-884Z',
            'X-CleverTap-Passcode'=>'QAA-IMW-CIAL' 
    ),
    // QA
    /* 'image_base_urls' => array (
    'local-api' => 'munchapi.hungrybuzz.info',
    'local-cms' => 'http://cms-qa.munchado.biz',
    'amazon' => 'qa.hungrybuzz.info/munch_images/'
    ) */
    //Clickatell Details
    'clikcatell' => array(
        'cat_auth_url' => 'http://api.clickatell.com/http/auth',
        'cat_sendmsg_url' => 'http://api.clickatell.com/http/sendmsg',
        'cat_api_id' =>'3553449',
        'cat_ac_username'=>'msaxena', 
        'cat_ac_password'=>'MKVEPLUBfEWWDI', 
        'cat_from'=>'919891778524', //the no of handset the message must be delivered
        'country_code_us'=>'1'
    ),
    'bitly' => array(
        'bit_login' =>'rkhatak',
        'bit_app_key'=>'R_105007a96c804e8fa0c54afd79c365b5', 
    ),
//    'ga' => array(
//            'google_analytics_projectid' => '86242422',
//            'authfile' => BASE_DIR . DS .'vendor/qc project-5ea18e4270b4.json',
//    ),
        // QA
        /*  'image_base_urls' => array (
          'local-api' => 'munchapi.hungrybuzz.info',
          'local-cms' => 'http://cms-qa.munchado.biz',
          'amazon' => 'qa.hungrybuzz.info/munch_images/'
          ) */
);
