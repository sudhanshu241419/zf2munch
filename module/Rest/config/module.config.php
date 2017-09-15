<?php

return array(
    // Api Standards
    'api_standards' => array(
        // Default text 'token'
        'token_text' => 'token',
        'formatter_text' => 'response_type',
        'default_formatter' => 'json',
        'default_ttl' => 315360000
    ),
    'errors' => array(
        'show_exceptions' => array(
            'message' => true,
            'trace' => true
        )
    ),
    'di' => array(
        'instance' => array(
            'alias' => array(
                'json_processor' => 'Rest\Processors\Json',
                'image_processor' => 'Rest\Processors\Image',
                'xml_processor' => 'Rest\Processors\Xml',
                'phps_processor' => 'Rest\Processors\Phps'
            )
        )
    ),
    'city_timezones' => array(
        'SF' => 'America/Los_Angeles',
        'NY' => 'America/New_York',
        'IN' => 'Asia/Kolkata',
        'CA' => 'America/Los_Angeles'
    )
);
