<?php
require_once 'init.php';

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Mime;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

$application_env = getenv('APPLICATION_ENV');
$version = "26";

if ($application_env == 'qaa' || $application_env == 'demo' || $application_env == 'live') {
    // data
    $data = array(

       'to' => "hungrytech@hungrybuzz.info",
        //'to' => "ptrivedi@adventit.in",
        'from' => "deploy@munchado.com",
        'body_text' => "Deployed MunchAdo " . $version . " on " . $application_env ,
        'reply_to' => "ptrivedi@adventit.in",
        'subject' => "Deployed VERSION-" . $version . " on " . $application_env . " <EOM>"
    );
    // email class initialization
    $mail = new Message();
    
    // set html body content
    $html = new MimePart($data['body_text']);
    $html->type = "text/html";
    
    // attach file with known mime type and file name
    $doc = new MimePart(fopen(__DIR__ . "/doc/Releasenote-B26.pdf", 'r'));
    $doc->filename = "Releasenote-B26.pdf";
    $doc->encoding = Mime::ENCODING_BASE64;
    $doc->type = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
    $doc->disposition = Mime::DISPOSITION_ATTACHMENT;
    
    // prepare email
    $mail->addFrom($data['from']);
    $mail->addTo($data['to']);
    $mail->addReplyTo($data['reply_to']);
    
    $body = new MimeMessage();
    $body->setParts(array(
        $html,
        $doc
    ));
    $mail->setSubject($data['subject']);
    $mail->setEncoding("UTF-8");
    
    $mail->setBody($body);
    $transport = new SmtpTransport();
    $options = new SmtpOptions(array(
        'name' => 'munchado.com',
        'host' => 'email-smtp.us-east-1.amazonaws.com',
        'port' => 25, // Notice port change for TLS is 587
        'connection_class' => 'login',
        'connection_config' => array(
            'username' => 'AKIAJ7DBIS7JU75GS5GA',
            'password' => 'AswIKyz+lVt6FitW8hu4fqRUjYvLBTTbzwa1kVu4I3Q8',
            'ssl' => 'tls'
        )
    ));
    $transport->setOptions($options);
    try {
        $transport->send($mail);
    } catch (\Exception $ex) {
        echo $ex->getMessage();
    }
}

?>