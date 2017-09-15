<?php
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'local'));
$attachment_location = dirname(__DIR__).'/public/assets/download/xml/allrestaurant.xml';
if (file_exists($attachment_location)) {

    header('Content-type: text/xml');
    header("Content-Transfer-Encoding: Binary");
    header("Content-Disposition: attachment; filename=restaurant.xml");
    readfile($attachment_location);
    die();
} else {
    die("Error: File not found.");
}
?>