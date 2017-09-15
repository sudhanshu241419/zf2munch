<?php
namespace Aboutus\Controller;

use MCommons\Controller\AbstractRestfulController;

class AboutusController extends AbstractRestfulController
{
    public function getList()
    {
        $response = array();
        $response['aboutus']['heading']="What's the Big Ado?";
        
        $response['aboutus']['p1']['subheading']="One To the Power of Three";
        $response['aboutus']['p1']['discription']="We're not just some one trick pony; you can discover your new favorite restaurant, order lunch and reserve a table for dinner without ever leaving Munch Ado. With so many restaurants; you'll be here forever and ever and ever.";
        $response['aboutus']['p1']['video_url'] = "http://s3.amazonaws.com/munchado/assets/mov/webm/Screen_one.webmhd.webm";
                
        $response['aboutus']['p2']['subheading']="Some is Good. More is Better.";
        $response['aboutus']['p2']['discription']="That's how we feel about menus. Sure having your favorites around the house is nice, but having menus for all your possible favorite places when you're not sure what to eat is borderline genius. Not to toot our own horn or anything, but this is our house and we have more menus than you. Way more. Like, nearly every menu this side of the Rockies more. And they're all fully searchable…at the same time.";
        $response['aboutus']['p2']['video_url'] ="http://s3.amazonaws.com/munchado/assets/mov/webm/Screen_two.webmhd.webm";
        
        $response['aboutus']['p3']['subheading']="Meals are Greater Than the Sum of Their Parts";
        $response['aboutus']['p3']['discription']="And restaurants are more than tables, chairs, chefs, and delivery men. They all have a story to tell and MunchAdo.com is helping them tell it. Some were born with a silver spatula in their mouth while others baked their way across country sides and oceans; we're here to help all of them share their stories and their food with you.";
        $response['aboutus']['p3']['video_url'] ="http://s3.amazonaws.com/munchado/assets/mov/webm/Screen_three.webmhd.webm";
        
        $response['aboutus']['p4']['subheading']="Your Order Doesn't End At Checkout";
        $response['aboutus']['p4']['discription']="Munch Ado keeps tabs on your ticket as it moves through the kitchen, out the door and into your home. We even double and triple check your reservations making sure you're never left out in the cold, rain, heat or other natural disaster.";
        $response['aboutus']['p4']['video_url'] ="http://s3.amazonaws.com/munchado/assets/mov/webm/Screen_four.webmhd.webm";
        
        $response['aboutus']['p5']['subheading']="Food Technology Isn’t All Gizmos and Gadgets";
        $response['aboutus']['p5']['discription']="With a revolutionary search, and a few new ideas, Munch Ado is changing the way users discover restaurants. Picky eaters can now find every place within 10 square miles that makes a mean bowl of Mac and Cheese. Arranging a group order with friends and creating your own cheesy dinner party has never been easier.";
        $response['aboutus']['p5']['video_url'] ="http://s3.amazonaws.com/munchado/assets/mov/webm/Screen_five.webmhd.webm";
        
        $response['aboutus']['p6']['subheading']="All Life’s a Game and You Need Points to Win";
        $response['aboutus']['p6']['discription']="Don’t worry; we’re giving them out for free and with nearly everything you do on the site. You can use them towards your next order, redeem* them or hoard them until you have the most!";
        $response['aboutus']['p1']['video_url'] = "http://s3.amazonaws.com/munchado/assets/mov/webm/Screen_six.webmhd.webm";
        
        return $response;
    }
}
	