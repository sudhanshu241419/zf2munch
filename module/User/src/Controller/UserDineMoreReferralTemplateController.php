<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;

class UserDineMoreReferralTemplateController extends AbstractRestfulController {
    public function create($data) {
            if((isset($data['inviter_name']) && $data['inviter_name']!=''))
            {
            $commonFunctions = new \MCommons\CommonFunctions();    
            $config = $this->getServiceLocator()->get('Config'); 
            $siteUrl=PROTOCOL.$config['constants']['web_url'];  
            $img=TEMPLATE_IMG_PATH;
            $inviterName = $data['inviter_name'];
            $restaurantName ="";
            
            if((isset($data['restaurant_name']) && $data['restaurant_name']!='')){
             $restaurantName = $commonFunctions->modifyRestaurantName($data['restaurant_name']);
            } 
            $joinnow = $data['join_now'];
            if($restaurantName){
               $getTemplate=$this->template($inviterName,$restaurantName,$joinnow,$siteUrl,$img); 
            }else{
               $getTemplate=$this->matemplate($inviterName,$joinnow,$siteUrl,$img);  
            }
            $data['template']=$getTemplate;
            }else{
             $data['error']='Inviter Name is required';   
            }
        return $data;
    }
    //Template for Dine More
    public function template($inviterName=false,$restaurantName=false,$joinnow=false,$siteUrl=false,$imgP=false){
        $str='';
        $str.='<!DOCTYPE html>
  <html style="width: 100%;">
     <head>
        <title>Munch Ado | Dine & More Rewards!</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1.0, user-scalable=no">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <style type="text/css">
           @-ms-viewport {width: device-width;}
           @media screen and (max-device-width: 400px), screen and (max-width: 400px) {
           img[id="dv1"], img[id="dv2"] {display: none !important;}
           }
           @media only screen and (max-device-width: 414px) {
              .d_i{display:none!important;}
              .m_i{display:block!important;}
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
                       <td align="center" style="font-size:0">
                       <a href="'.$siteUrl.'" style="border: none; text-decoration: none; outline: none;" target="_blank">
                          <img src="'.$imgP.'header_dinein_1.jpg" alt="" style="border: none; width: 100%; max-width: 100%; height: auto;display:block">
                        </a>      
                       </td>
                    </tr>
                    <tr>
                       <td bgcolor="#ffffff" align="left" valign="top" style="padding-left:30px;padding-right:30px;">
                          <table bgcolor="#ffffff" align="left" width="100%" border="0" border-collapse="collapse" cellpadding="0" cellspacing="0" style="width: 100%;max-width: 490px;">
                             <tr>
                                <td bgcolor="#ffffff" style="font-family:arial; font-size:18px;padding-top:35px;line-height:24px;padding-bottom:39px;color:#333333;text-align:left">
                                Your friend <strong>'.$inviterName.'</strong> wants to share the joys of <strong>'.$restaurantName.'</strong> Dine & More rewards program, presented by Munch Ado with you!<br /><br />Dine & More members are treated to irresistible rewards, free food and oh so much more. 
                                </td>
                             </tr>
                             <tr>
                               <td>
                                 <table align="left" bgcolor="#fff" width="100%" border="0" cellpadding="0" cellspacing="0" style="width: 100%; max-width: 550px;">
                                   <tr>
                                     <td align="left" valign="top" style="font-size:0;padding-right:10px;padding-left:10px;">
                                       <img src="'.$imgP.'i_vip_perks.png" style="display:block">
                                     </td>
                                     <td style="font-family:arial; font-size:18px;line-height:24px;color:#333333;text-align:left;padding-bottom:20px;"><strong>VIP Perks & Flash Offers</strong><br />Enjoy access to perks like priority seating on reservations and special flash offers like retailer credits and bonus loyalty points.
                                     </td>
                                   </tr>
                                   <tr>
                                     <td align="left" valign="top" style="font-size:0;padding-right:10px;padding-left:10px;">
                                       <img src="'.$imgP.'i_loyalty_points.png" style="display:block">
                                     </td>
                                     <td style="font-family:arial; font-size:18px;line-height:24px;color:#333333;text-align:left;padding-bottom:20px;"><strong>Loyalty Points</strong><br />Earn points for sharing your dining experiences with us when you write reviews, refer friends, book tables, and more online.
                                     </td>
                                   </tr>
                                   <tr>
                                     <td align="left" valign="top" style="font-size:0;padding-right:10px;padding-left:10px;">
                                       <img src="'.$imgP.'i_exclusiverewards.png" style="display:block">
                                     </td>
                                     <td style="font-family:arial; font-size:18px;line-height:24px;color:#333333;text-align:left;padding-bottom:20px;"><strong>Exclusive Rewards & Specials</strong><br />Receive discounted and delicious food specials and rewards just for select Dine & More members.
                                     </td>
                                   </tr>
                                 </table>
                               </td>
                             </tr>
                             <tr>
                               <td style="font-family:arial; font-size:18px;text-align:center;padding-top:15px;padding-bottom:10px;">
                                 Join today and never dine the same way again. 
                               </td>
                             </tr>
                             <tr>
                              <td  bgcolor="#ffffff" style="padding-top:8px;padding-bottom:8px;" valign="middle" align="center">
                                 <!--[if mso]>
                                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="'.$joinnow.'" style="height:60px;v-text-anchor:middle;width:460px;" arcsize="9%" stroke="f" fillcolor="#a2cd01">
                                              <w:anchorlock/>
                                              <center>
                                            <![endif]-->
                                            <a href="'.$joinnow.'" target="_blank"
                                               style="background-color:#a2cd01;border-radius:6px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:1.5em;font-weight:normal;padding:13px 5px;text-align:center;text-decoration:none;width:100%;max-width:460px;-webkit-text-size-adjust:none;text-transform: uppercase;">Join Now</a>
                                            <!--[if mso]>
                                                </center>
                                              </v:roundrect>
                                            <![endif]-->
                              </td>
                           </tr>
                            <tr>
                                <td style="padding-top:41px;padding-bottom:42px;" align="center">
                                    <table border="0" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                        <tr>
                            <td style="width: 30%; max-width: 158px;" align="left">
                                <a href="' . $siteUrl . '/mypoints" target="_blank" style="text-decoration:none;">
                                        <img src="' . $imgP . '/i_paywithpoint.jpg" border="0" style="max-width:158px;width:100%;display: block;">
                                   </a>
                            </td>
                                <td style="width: 5%; max-width: 25px;" align="left">&nbsp;</td>
                                <td style="width: 30%; max-width: 158px;" align="left">
                                    <a href="' . $siteUrl . '/myspecials" target="_blank" style="text-decoration:none;">
                                        <img src="' . $imgP . '/i_seesprcials.jpg" border="0" style="max-width:158px;width:100%;display: block;">
                                    </a>
                                </td>
                                <td style="width: 5%; max-width: 25px;" align="left">&nbsp;</td>
                                <td style="width: 30%; max-width: 158px;" align="left">
                                    <a href="' . $siteUrl . '/loyalty-points" target="_blank" style="text-decoration:none;">
                                        <img src="' . $imgP . '/i_learnhowpoint.jpg" border="0" style="max-width:158px;width:100%;display: block;">
                                    </a>
                                </td>
                            </tr>
                            </table>
                                </td>
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
                                  <td style="font-size:12px; font-family: Arial; color:#3d3a39; text-align:center;padding-top:3px;">POWERED BY</td>
                                </tr>
                                <tr>
                                  <td style="text-align:center;padding-top:6px;padding-bottom:21px;">
                                    <img src="'.$imgP.'ma_footer_logo.png">
                                  </td>
                                </tr>
                                  <tr>
                                      <td style="font-size: .875em; font-family: Arial; color:#3d3a39;" align="center">FOLLOW US</td>
                                  </tr>
                                  <tr>
                                      <td style="padding-top: 19px;" align="center">
                                          <span style="display: inline-block; width: auto; overflow: hidden; width: 100%; max-width: 275px;">
                                              <a href="https://www.facebook.com/munchado" target="_blank" style="display: inline-block;">
                                               <img src="'.$imgP.'facebook-icon.png" alt="Facebook" width="65" height="65" border="0">
                                               </a>
                                               <img id="dv1" src="'.$imgP.'devider.jpg" alt="" width="6">
                                               <a href="http://twitter.com/Munch_Ado" style="display: inline-block;">
                                               <img src="'.$imgP.'twitter-icon.png" alt="Twitter" width="65" height="65" border="0">
                                               </a>
                                               <img id="dv2" src="'.$imgP.'devider.jpg" alt="" width="6">
                                               <a href="http://www.instagram.com/munchado" target="_blank" style="display: inline-block;">
                                               <img src="'.$imgP.'instagram-icon.png" alt="Instagram" width="65" height="65" border="0">
                                               </a>
                                          </span>
                                      </td>
                                  </tr>
                                  <tr>
                                      <td align="center" style="padding: 35px 0 40px;">
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
                                    <td align="center" style="padding:0 0 40px;">
                                      <a href="https://itunes.apple.com/us/app/munch-ado/id1024973395?ls=1&mt=8" target="_blank"><img src="'.$imgP.'btn_gettheapp.png" alt="" style="max-width:100%"></a>
                                    <td>
                                </tr>
                                  <tr>
                                      <td style="font-size: .938em; line-height: 1.250em; font-family: Arial; color:#3d3a39; text-align:left;" align="left">
                                          245 5th Avenue, Suite 1002, New York, NY 10016<br>
                                          Copyright 2013-2016, Munch Ado LLC. All rights reserved.<br /><br />Toll Free: <strong>1-888-345-1303</strong>
                                      </td>
                                  </tr>
                                  <tr>
                                      <td style="padding-top: 15px; font-size:15px; font-weight: bold; font-family: Arial;text-align:left;color:#3d3a39;">
                                          <a href="'.$siteUrl.'/terms" target="_blank" style="text-decoration:none; outline: none; color: #ff8207;">Terms</a> | <a href="'.$siteUrl.'/privacy" target="_blank" style="text-decoration:none;outline: none; color: #ff8207;">Privacy Policy</a> | <a href="'.$siteUrl.'/support" target="_blank" style="text-decoration:none;outline: none; color: #ff8207;">FAQs &amp; Customer Support</a>
                                      </td>
                                  </tr>

                              </table>
                       </td>
                    </tr>
                    <tr>
                       <td style="background: #3d3a39; font-size:15px;color: #fff;line-height:20px; font-family: Arial;text-align:left;padding-left:30px;padding-right:30px;padding-top:20px;padding-bottom:20px;">To make sure our email updates are delivered to your inbox, please add <a href="mailto:wecare@munchado.com" style="text-decoration:none;color: #ff8207; font-weight: bold; outline: none;">WeCare@MunchAdo.com</a> to your email Address Book.
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
    //Template for MA 
    public function matemplate($inviterName = false,$joinnow = false, $siteUrl = false, $imgP = false) {
        $str = '';
        $str.='<!DOCTYPE html>
<html style="width: 100%;">
    <head>
        <title>Munch Ado | Dine & More Rewards!</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1.0, user-scalable=no">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <style type="text/css">
            @-ms-viewport {width: device-width;}
            @media screen and (max-device-width: 400px), screen and (max-width: 400px) {
                img[id="dv1"], img[id="dv2"] {display: none !important;}
            }
            @media only screen and (max-device-width: 414px) {
              .d_i{display:none!important;}
              .m_i{display:block!important;}
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
                <td style="background-color:#3e3a39; text-align: center;padding-top:18px;padding-bottom:9px;" bgcolor="#3d3a39" align="center" valign="top">
                        <a href="'.$siteUrl.'" style="border: none; text-decoration: none; outline: none;" target="_blank">
                        <img src="'.$imgP.'ma_logo.jpg" alt="'.$siteUrl.'" style="border: none; height: 59px; width: 250px;" height="59" width="250">
                        </a>
                </td>
               
            </tr>
            <tr>
                <td bgcolor="#ffffff" align="left" valign="top" style="padding-left:30px;padding-right:30px;">
                    <table bgcolor="#ffffff" align="left" width="100%" border="0" border-collapse="collapse" cellpadding="0" cellspacing="0" style="width: 100%;max-width: 490px;">
                        <tr>
                            <td bgcolor="#ffffff" style="font-family:arial; font-size:18px;padding-top:35px;line-height:24px;padding-bottom:39px;color:#333333;text-align:left">
                                Your friend <strong>'.$inviterName.'</strong> wants to share the joys of <strong>Munch Ado</strong> with you!
                            </td>
                        </tr>
                        <tr>
                                <td bgcolor="#ffffff" style="font-family:arial; font-size:18px;line-height:24px;color:#333333;text-align:left">At Munch Ado, we do all things dining and we help you do them best. We\'re all about finding what you crave and eating it. We help you go 
                                on food adventures all over town, and find the best dining experiences in your neighborhood.</td>
                        </tr>
                        
                        <tr><td bgcolor="#ffffff" style="font-family:arial; font-size:18px;line-height:54px;color:#333333;text-align:left">Use '.$inviterName.'\'s personal link to join now.</td></tr>
                        
                        <tr> 
                            <td style="padding: 17px 0 24px;">
                                <!--[if mso]>
                                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="'.$joinnow.'" style="height:66px;v-text-anchor:middle;width:486px;" arcsize="9%" stroke="f" fillcolor="#eeeeee">
                                   <w:anchorlock/>
                                   <center>
                                <![endif]-->
                                <table align="center" width="100%" border="0" cellpadding="0" cellspacing="0" style="width: 100%;  color: #3d3a39; font: normal 1.125em/1.375em Arial; text-align: center;background-color: #eeeeee; border-radius: 6px;">
                                    <tbody>
                                        <tr>
                                            <td style="text-align: center" valign="middle" align="center" height="60">
                                                <a href="'.$joinnow.'" target="_blank" class="visitTheMunchAdoBogBtn" style="font-family: sans-serif; font-size: 1em; font-weight: bold; line-height: 1em; text-align: center; text-decoration: none; width: 100%; max-width: 486px; -webkit-text-size-adjust: none; text-transform: lowercase; overflow: hidden; text-overflow: ellipsis;color: #ff8208;">'.$joinnow.'</a>
                                            </td>
                                        </tr>
                                   </tbody>
                                </table>
                                <!--[if mso]>
                                </center>
                                </v:roundrect>
                                <![endif]-->
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#ffffff" style="font-family:arial; font-size:18px;padding-top:35px;line-height:24px;padding-bottom:9px;color:#333333;text-align:left">
                               We\'ll get you started on your first food adventure with 100 loyalty points when you join and another 15 for using '.$inviterName.'\'s referral link. As you earn more points, you can use them towards your meals!
                            </td>
                        </tr>
                        <tr>
                            <td  bgcolor="#ffffff" style="padding-top:8px;padding-bottom:8px;" valign="middle" align="center">
                                <!--[if mso]>
                                           <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="'.$joinnow.'" style="height:60px;v-text-anchor:middle;width:460px;" arcsize="9%" stroke="f" fillcolor="#a2cd01">
                                             <w:anchorlock/>
                                             <center>
                                           <![endif]-->
                                <br />
                                    <a href="'.$joinnow.'" target="_blank"
                                       style="background-color:#a2cd01;border-radius:6px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:1.5em;font-weight:normal;padding:13px 5px;text-align:center;text-decoration:none;width:100%;max-width:460px;-webkit-text-size-adjust:none;text-transform: uppercase;">Join Now & Earn Points</a>
                                <!--[if mso]>
                                    </center>
                                  </v:roundrect>
                                <![endif]-->
                            </td>
                        </tr>
                        <tr>
                                <td style="padding-top:41px;padding-bottom:42px;" align="center">
                                    <table border="0" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
                        <tr>
                            <td style="width: 30%; max-width: 158px;" align="left">
                                <a href="' . $siteUrl . 'mypoints" target="_blank" style="text-decoration:none;">
                                        <img src="' . $imgP . '/i_paywithpoint.jpg" border="0" style="max-width:158px;width:100%;display: block;">
                                   </a>
                            </td>
                                <td style="width: 5%; max-width: 25px;" align="left">&nbsp;</td>
                                <td style="width: 30%; max-width: 158px;" align="left">
                                    <a href="' . $siteUrl . '/myspecials" target="_blank" style="text-decoration:none;">
                                        <img src="' . $imgP . '/i_seesprcials.jpg" border="0" style="max-width:158px;width:100%;display: block;">
                                    </a>
                                </td>
                                <td style="width: 5%; max-width: 25px;" align="left">&nbsp;</td>
                                <td style="width: 30%; max-width: 158px;" align="left">
                                    <a href="' . $siteUrl . '/loyalty-points" target="_blank" style="text-decoration:none;">
                                        <img src="' . $imgP . '/i_learnhowpoint.jpg" border="0" style="max-width:158px;width:100%;display: block;">
                                    </a>
                                </td>
                            </tr>
                            </table>
                                </td>
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
                            <td style="font-size:12px; font-family: Arial; color:#3d3a39; text-align:center;padding-top:3px;">POWERED BY</td>
                        </tr>
                        <tr>
                            <td style="text-align:center;padding-top:6px;padding-bottom:21px;">
                                <img src="'.$imgP.'ma_footer_logo.png">
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: .875em; font-family: Arial; color:#3d3a39;" align="center">FOLLOW US</td>
                        </tr>
                        <tr>
                            <td style="padding-top: 19px;" align="center">
                                <span style="display: inline-block; width: auto; overflow: hidden; width: 100%; max-width: 275px;">
                                    <a href="https://www.facebook.com/munchado" target="_blank" style="display: inline-block;">
                                        <img src="'.$imgP.'facebook-icon.png" alt="Facebook" width="65" height="65" border="0">
                                    </a>
                                    <img id="dv1" src="'.$imgP.'devider.jpg" alt="" width="6">
                                    <a href="http://twitter.com/Munch_Ado" style="display: inline-block;">
                                        <img src="'.$imgP.'twitter-icon.png" alt="Twitter" width="65" height="65" border="0">
                                    </a>
                                    <img id="dv2" src="'.$imgP.'devider.jpg" alt="" width="6">
                                    <a href="http://www.instagram.com/munchado" target="_blank" style="display: inline-block;">
                                        <img src="'.$imgP.'instagram-icon.png" alt="Instagram" width="65" height="65" border="0">
                                    </a>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="padding: 35px 0 40px;">
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
                            <td align="center" style="padding:0 0 40px;">
                                <a href="https://itunes.apple.com/us/app/munch-ado/id1024973395?ls=1&mt=8" target="_blank"><img src="'.$imgP.'btn_gettheapp.png" alt="" style="max-width:100%"></a>
                            <td>
                        </tr>
                        <tr>
                            <td style="font-size: .938em; line-height: 1.250em; font-family: Arial; color:#3d3a39; text-align:left;" align="left">
                                245 5th Avenue, Suite 1002, New York, NY 10016<br>
                                Copyright 2013-2016, Munch Ado LLC. All rights reserved.<br /><br />Toll Free: <strong>1-888-345-1303</strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 15px; font-size:15px; font-weight: bold; font-family: Arial;text-align:left;color:#3d3a39;">
                                <a href="'.$siteUrl.'/terms" target="_blank" style="text-decoration:none; outline: none; color: #ff8207;">Terms</a> | <a href="'.$siteUrl.'/privacy" target="_blank" style="text-decoration:none;outline: none; color: #ff8207;">Privacy Policy</a> | <a href="'.$siteUrl.'/support" target="_blank" style="text-decoration:none;outline: none; color: #ff8207;">FAQs &amp; Customer Support</a>
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
            <tr>
                <td style="background: #3d3a39; font-size:15px;color: #fff;line-height:20px; font-family: Arial;text-align:left;padding-left:30px;padding-right:30px;padding-top:20px;padding-bottom:20px;">To make sure our email updates are delivered to your inbox, please add <a href="mailto:notifications@munchado.com" style="text-decoration:none;color: #ff8207; font-weight: bold; outline: none;">notifications@munchado.com</a> to your email Address Book.
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
