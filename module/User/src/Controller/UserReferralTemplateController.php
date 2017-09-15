<?php

namespace User\Controller;

use User\Model\User;
use User\UserFunctions;
use MCommons\Controller\AbstractRestfulController;

class UserReferralTemplateController extends AbstractRestfulController {

    public function create($data) {
        if ((isset($data['referralStr']) && $data['referralStr'] != '') && (isset($data['referralCode']) && $data['referralCode'] != '')) {
            $config = $this->getServiceLocator()->get('Config');
            $siteUrl = PROTOCOL . $config['constants']['web_url'];
            $bitLeyAccess = $config['bitly'];
            $img = TEMPLATE_IMG_PATH;
            $login = $bitLeyAccess['bit_login'];
            $appkey = $bitLeyAccess['bit_app_key'];
            $bitSortUrl = trim($this->get_bitly_short_url($data['referralStr'], $login, $appkey));
            $getTemplate = $this->template($bitSortUrl, $data['referralCode'], $siteUrl, $img);
            $data['template'] = $getTemplate;
        } else {
            $data['error'] = 'Referral link and Referral code is required';
        }
        return $data;
    }

    public function get_bitly_short_url($url, $login, $appkey, $format = 'txt') {
        $connectURL = 'http://api.bit.ly/v3/shorten?login=' . $login . '&apiKey=' . $appkey . '&uri=' . urlencode($url) . '&format=' . $format;
        return $this->curl_get_result($connectURL);
    }

    public function curl_get_result($url) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public function template($rStr = false, $rCode = false, $siteUrl = false, $imgP = false) {
        $str = '';
        $str.='<!DOCTYPE html>
<html style="width: 100%;">
   <head>
      <title>Email Awarding : Munch Ado</title>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <meta name="viewport" content="width=device-width,initial-scale=1.0, user-scalable=no">
      <meta http-equiv="X-UA-Compatible" content="IE=edge" />
      <style type="text/css">
         @-ms-viewport {width: device-width;}
         @media screen and (max-device-width: 400px), screen and (max-width: 400px) {
         img[id="dv1"], img[id="dv2"] {display: none !important;}
         }
      </style>
   </head>
   <body style="margin: 0; padding: 0; font: normal 16px Arial; color:#333333; width: 100%;">
      <!--[if (gte mso 9)|(IE)]>
      <table width="550" align="center" cellpadding="0" cellspacing="0" border="0">
         <tr>
            <td>
               <![endif]-->
               <table align="center" bgcolor="#fff" width="100%" border="0" cellpadding="0" cellspacing="0" style="width: 100%; max-width: 550px;">
                  <tr>
                     <td align="center" bgcolor="#3d3a39" valign="top" style="background-color:#3e3a39; text-align: center;padding-top:18px;padding-bottom:9px;">
                        <a href="' . $siteUrl . '" target="_blank" style="border: none; text-decoration: none; outline: none;">
                        <img src="' . $imgP . 'ma_logo.jpg" alt="MunchAdo.com" width="250" height="59" style="border: none; height: 59px; width: 250px;">
                        </a>
                     </td>
                  </tr>
                  <tr>
                     <td align="center" style="font-size:0">
                     <a href="' . $rStr . '" target="_blank" style="border: none; text-decoration: none; outline: none;">
                        <img src="' . $imgP . 'header_invitee.jpg" alt="Email Awarding" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;min-height: 100px;">
                            </a>
                     </td>
                  </tr>
                  <tr>
                     <td align="left" valign="top" bgcolor="#ffffff" style="padding-left:30px;padding-right:30px;">
                        <table bgcolor="#ffffff" align="left" width="100%" border="0" cellpadding="0" cellspacing="0" style="width: 100%;max-width: 490px;">
                           <tr>
                              <td bgcolor="#ffffff" style="font-family:arial; font-size:18px; line-height:24px; color:#333333;padding-top:30px;"><strong>I&#8217;ve sold you out to Munch Ado</strong>, this new NYC food discovery platform where you can order food for delivery and takeout and book reservations online.<br /><br /><strong>Use my link or referral code to join and earn $5 cash back after your first order</strong></td>
                           </tr>
                           <tr>
                              <td style="padding: 17px 0 24px;">
                                       <table align="center" width="100%" border="0" cellpadding="0" cellspacing="0" style="width: 100%; max-width: 486px; color: #3d3a39; font: normal 1.125em/1.375em Arial; text-align: center;background-color: #eeeeee; border-radius: 6px;">
                                          <tr>
                                             <td style="text-align: center" valign="middle" align="center" height="60">
                                                <a href="' . $rStr . '" target="_blank" class="visitTheMunchAdoBogBtn"
                                                   style="font-family: sans-serif; font-size: 1em; font-weight: bold; line-height: 1em; text-align: center; text-decoration: none; width: 100%; max-width: 486px; -webkit-text-size-adjust: none; text-transform: lowercase; overflow: hidden; text-overflow: ellipsis;color: #ff8208;">' . $rStr . '</a>
                                             </td>
                                          </tr>
                                          <tr>
                                             <td style="font:15px arial">or enter this code at the time of registration</td>
                                          </tr>
                                           <tr>
                                             <td style="padding-top:8px;padding-bottom:20px;">
                                              <table style="background: #ffffff;display: inline-block;padding: 4px 20px;border: 1px solid #c8c8c8;font-weight: bold;color: #000;">
                                               <tr>
                                                <td style="font-family: arial;font-size: 20px;">' . $rCode . '</td>
                                               </tr>
                                              </table>
                                             </td>
                                          </tr>
                                          
                                       </table>
                              </td>
                           </tr>
                           <tr>
                              <td bgcolor="#ffffff" style="font-family:arial; font-size:18px; line-height:24px; color:#333333;">If you join and place an order online, I get cold, hard cash-like currency and you can earn $30 of your own like I&#8217;m trying to do. So, what I&#8217;m saying is&#8230;do it!
                              </td>
                           </tr>
                           <tr>
                              <td bgcolor="#ffffff" style="padding-top:36px;padding-bottom:36px;" valign="middle" align="center">
                                 <!--[if mso]>
                                 <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="' . $siteUrl . '" style="height:60px;v-text-anchor:middle;width:460px;" arcsize="9%" stroke="f" fillcolor="#a2cd01">
                                    <w:anchorlock/>
                                    <center>
                                       <![endif]-->
                                       <a href="' . $rStr . '" target="_blank" class="learAboutLoyality"
                                          style="background-color:#a2cd01;border-radius:6px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:1.5em;font-weight:normal;padding:13px 0;text-align:center;text-decoration:none;width:100%;max-width:460px;-webkit-text-size-adjust:none;text-transform: uppercase;">Join Now & Get $5 Cash Back</a>
                                       <!--[if mso]>
                                    </center>
                                 </v:roundrect>
                                 <![endif]-->
                              </td>
                           </tr>
                           <tr>
                              <td bgcolor="#ffffff" style="padding-bottom:19px;font-family:arial;font-size:18px;line-height:24px;">For now though,<br />We must bid you Munch Ado</td>
                           </tr>
                        </table>
                     </td>
                  </tr>
                  <tr>
                     <td align="center" valign="top">
                        <table align="center" width="100%" border="0" cellpadding="0" cellspacing="0" style="width: 100%; max-width: 550px;">
                           <tr>
                              <td valign="top" align="center" style="padding: 20px; background: #f6f8f1;">
                                 <table align="center" width="100%" border="0" cellpadding="0" cellspacing="0" style="width: 100%; max-width: 490px;">
                                    
                                <tr>
                                    <td style="font-size: .875em; font-family: Arial; color:#3d3a39;" align="center">Presented BY</td>
                                </tr>
                                

                                <tr>
                                    <td style="text-align:center;padding-bottom:10px;">
                                        <a href="'.$siteUrl.'" target="_blank">
                                            <img src="'.$imgP.'logo_munchado.png" alt="Munch Ado" style="border:0;max-width:100%;">
                                        </a>
                                    </td>
                                </tr>


                                    <tr>
                                       <td style="font-size: .875em; font-family: Arial; color:#3d3a39;" align="center">FOLLOW US</td>
                                    </tr>
                                    <tr>
                                       <td style="padding-top: 19px;" align="center">
                                          <span style="display: inline-block; width: auto; overflow: hidden; width: 100%; max-width: 275px;">
                                          <a href="https://www.facebook.com/munchado" target="_blank" style="display: inline-block;">
                                          <img src="' . $imgP . 'facebook-icon.png" alt="Facebook" width="65" height="65" border="0">
                                          </a>
                                          <img id="dv1" src="' . $imgP . 'devider.jpg" alt="" width="6">
                                          <a href="http://twitter.com/Munch_Ado" style="display: inline-block;">
                                          <img src="' . $imgP . 'twitter-icon.png" alt="Twitter" width="65" height="65" border="0">
                                          </a>
                                          <img id="dv2" src="' . $imgP . 'devider.jpg" alt="" width="6">
                                          <a href="http://www.instagram.com/munchado" target="_blank" style="display: inline-block;">
                                          <img src="' . $imgP . 'instagram-icon.png" alt="Instagram" width="65" height="65" border="0">
                                          </a>
                                          </span>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td align="center" style="padding: 25px 0 40px;">
                                          <!--[if mso]>
                                          <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="http://www.munchadomag.com/" style="height:69px;v-text-anchor:middle;width:325px;" arcsize="9%" stroke="f" fillcolor="#d4d9c7">
                                             <w:anchorlock/>
                                             <center>
                                                <![endif]-->
                                                <a href="http://www.munchadomag.com/" target="_blank" style="background-color:#d4d9c7;border-radius:6px;color:#45484a;display:inline-block;font-family:sans-serif;font-size:17px;font-weight:normal;padding-top:20px;padding-bottom:20px;text-align:center;text-decoration:none;width:100%;max-width:325px;-webkit-text-size-adjust:none;">VISIT THE MUNCH ADO MAG</a>
                                                <!--[if mso]>
                                             </center>
                                          </v:roundrect>
                                          <![endif]-->
                                       </td>
                                    </tr>
                                   
                                    <tr>
                                    <td style="text-align:center; padding-bottom:30px">
                                        <a href="https://play.google.com/store/apps/details?id=com.munchado&hl=en" target="_blank">
                                            <img src="'.$imgP.'btn_googleplay.png" alt="Download it from google play" style="border:0;max-width:100%;"></a>
                                        <a href="https://itunes.apple.com/us/app/munch-ado/id1024973395?ls=1&mt=8" target="_blank">
                                            <img src="'.$imgP.'btn_iphone.png" alt="Download it from apple store" style="border:0;max-width:100%;">
                                        </a>
                                    </td>
                                    </tr>

                                    <tr>
                                       <td style="font-size: .938em; line-height: 1.250em; font-family: Arial; color:#3d3a39; text-align:left;" align="left">
                                          <a style="text-decoration:none;color:#3d3a39;text-align:left;cursor:text;">245 5th Avenue Suite 1002, New York, NY 10016</a><br>
                                          Copyright 2013. Munch Ado LLC. All rights reserved.<br /><br />Toll Free: <a href="tel:1-888-345-1303" style="font-weight:bold;color:#3d3a39;text-decoration:none">1-888-345-1303</a>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td style="padding-top: 15px; font-size:15px; font-weight: bold; font-family: Arial;text-align:left;color:#3d3a39;">
                                          <a href="' . $siteUrl . '/terms" target="_blank" style="text-decoration:none; outline: none; color: #ff8207;">Terms</a> | <a href="' . $siteUrl . '/privacy" target="_blank" style="text-decoration:none;outline: none; color: #ff8207;">Privacy Policy</a> | <a href="' . $siteUrl . '/support" target="_blank" style="text-decoration:none;outline: none; color: #ff8207;">FAQs &amp; Customer Support</a>
                                       </td>
                                    </tr>
                                 </table>
                              </td>
                           </tr>
                           <tr>
                              <td style="background: #3d3a39; font-size:15px;color: #fff;line-height:20px; font-family: Arial;text-align:left;padding-left:30px;padding-right:30px;padding-top:20px;padding-bottom:20px;">To make sure our email updates are delivered to your inbox, please add <a href="mailto:wecare@munchado.com" style="text-decoration:none;color: #ff8207; font-weight: bold; outline: none;">notifications@munchado.com</a> to your email Address Book.
                              </td>
                           </tr>
                        </table>
                     </td>
                  </tr>
               </table>
               <!--[if (gte mso 9)|(IE)]>
            </td>
         </tr>
      </table>
      <![endif]-->
   </body>
</html>';
        return $str;
    }

}
