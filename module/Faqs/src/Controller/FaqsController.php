<?php
namespace Faqs\Controller;

use MCommons\Controller\AbstractRestfulController;

class FaqsController extends AbstractRestfulController
{
    public function getList()
    {
        $response = array();
        
        $response['faqs']['menu1']="GENERAL";
        $response['faqs']['menu2']="ORDERING DELIVERY/TAKEOUT";
        $response['faqs']['menu3']="TABLE RESERVATIONS";
        $response['faqs']['menu4']="BUYING DEALS OR COUPONS";
        $response['faqs']['menu5']="PAYMENTS";
        $response['faqs']['menu6']="REFUNDS & CANCELLATIONS";
        $response['faqs']['menu7']="PASSWORD";
        $response['faqs']['menu8']="EMAIL NOTIFICATIONS";
        
        $response['faqs']['contactus']['maintitle']='Still Stumped?';
        $response['faqs']['contactus']['subtitle']='Contact us';
        $response['faqs']['contactus']['call']='CALL 1-888-345-1303';
        $response['faqs']['contactus']['emailus']['title']='EMAIL US';
        $response['faqs']['contactus']['emailus']['emailcontent']="We're available 24/7/365 and all day on leap days";	
        
        $response['faqs']['heading']="Most Frequently Asked Frequently Asked Questions";
        
        $response['faqs']['general']['title']="GENERAL";
        $response['faqs']['general']['question1']="So this is customer support? How will you support me?";
        $response['faqs']['general']['answer1'] = "We're so very glad you asked. The first thing to know is that we're here for you. The second is, we're also there for you, but that's almost redundant.
We will fully support anything you Ado. Give us a call at 1-888-345-1303 anytime between 11:30 AM and 9:00 PM PST and we'll talk turkey, business and at length about any questions you may have or may not have. If you're more of the early night owl gets the worm type, feel free to email us at WeCare@MunchAdo.com after dark and during the wee hours of the morning.";
        $response['faqs']['general']['question2']="What is Munch Ado?";
        $response['faqs']['general']['answer2']="Munch Ado is where food meets service across the nation. We connect you with restaurants and your taste buds with the best food in town. And soon, every town.";
        $response['faqs']['general']['question3']="What's with the name? You trying to be clever or something?";
        $response['faqs']['general']['answer3']="So, everyone's talking. And when people talk, they tend to have opinions and an awful lot of them. Unlike in Shakespeare's play \"Much Ado About Nothing\" we're talking food. Hearty, meaty, juicy food. And here at Munch Ado, we're keeping the conversation going. We love having an informed and active user base and that's why we do what we a-do for you.";
        $response['faqs']['general']['question4']='And that tagline "From Soup to Nuts"? What were you thinking?';
        $response['faqs']['general']['answer4']="Okay, so everyone's a critic, but you know what? When you offer delivery, takeout and reservations from over 400,000 restaurants you really do have everything \"From Soup to Nuts.\"And since you're soooooooo critical, why don't you share your thoughts about your local favorites and we'll run it all through our big ol' Content Compiler 3000 as we sort restaurants and your results accordingly. Maybe then you'll stop analyzing and start enjoying. Jeez.";
        $response['faqs']['general']['question5']="Where can I use Munch Ado Beta*?";
        $response['faqs']['general']['answer5']="You can use it here or there. You will like it anywhere. Use it on your lap. Use it as an app.
*Right now, you can use Munch Ado in Southern Florida, San Jose and in its big sister city San Francisco which is famous for Rice-a-Roni. We think they have a pretty big bridge too. ";
        $response['faqs']['general']['question6']="What do those \"$\"s represent on those sliders?";
        $response['faqs']['general']['answer6']="Money of course! Money, money, money. Specifically, the money you'll likely spend per person. Here is a hand dandy chart for your reference. Feel free to print it out and have it laminated so it can live in your wallet. $ - Less than $8 $$ - Up to $15 $$$ - Up to $25 $$$$ - Up to $40 ";
        $response['faqs']['general']['question7']="These restaurant pages, is all that info correct?";
        $response['faqs']['general']['answer7']="Abso-positively. We worked our behinds off compiling all of that info and we're working our fingers to the bone continuing to check and recheck it with every restaurant.
That is to say, we do our best. Restaurants come and go long before most people even notice they changed their menus. That's why we're offering points to anyone who spots errors in our restaurant listings. Just send the error to AdoNot@MunchAdo.com with your name, account number and, if you're really cool, the correct data. In return, We'll credit your account with anywhere from 50 to 500 points. ";
        $response['faqs']['general']['question8']="I like/loathe your reviews & ratings system. How'd they come about?";
        $response['faqs']['general']['answer8']="Well, it's kind of a long story. So we're not going to tell it. Suffice to say, we know a guy who knows a guy who knows a gal. Along the chain, everyone is on the lookout for reviews all around the internet. We then take that info and wrap it all up into small digestible little bites for quick and easy digestion. We walk those bites around MunchAdo.com and let our users pick and choose what they like. Wow, this metaphor makes us sound like a waiter at some up-tight dinner party. Try imagining us wearing a black V-neck and jeans while we carry all that info on a SpongeBob Square Pants party plate. That's the kind of party we're throwing.
Anyway, back to the point. Our rating system takes into account the opinion of the Munch Ado community and the internet community at large to deliver the best information on each restaurant to our users.-Cool Story Brah.";
        
        
        $response['faqs']['ordering_delivery_takeout']['title']="ORDERING DELIVERY/TAKEOUT";
        $response['faqs']['ordering_delivery_takeout']['question1']="How do I place an order?";
        $response['faqs']['ordering_delivery_takeout']['answer1']="Well first, you have to select \"ORDER\". A-doy. Then enter your location, pick a restaurant, browse their menu, hit \"ADD TO ORDER\", and then just hit \"CHECKOUT\". You'll then enter all of the particulars Munch Ado needs to convince a restaurant to make your food (this is where you pay upfront cause money talks). Once all that is in, you confirm everything, sit back, relax and wait for your doorbell to buzz or the knock, knock, knock on your door, door, door.";
        $response['faqs']['ordering_delivery_takeout']['question2']="Why no love for COD (Cash on Delivery)?";
        $response['faqs']['ordering_delivery_takeout']['answer2']="We love COD, our finance guys though... they're sticklers. Sorry everyone.";
        $response['faqs']['ordering_delivery_takeout']['question3']="How do I get/eat food?";
        $response['faqs']['ordering_delivery_takeout']['answer3']="We let the restaurants handle their delivery systems their way. You may eat food off of a squircle plate at the restaurant or have a boy or girl deliver your food by car, bike or moped. We like to let everyone do their thing, but we'll call and check on your orders and reservations to make sure everything is going smoothly. We like to think of ourselves as lovable and helpful middlemen.";
        $response['faqs']['ordering_delivery_takeout']['question4']="How do I create a group order?";
        $response['faqs']['ordering_delivery_takeout']['answer4']="It's actually pretty simple. All you have to do is choose the restaurant. Add your order. And then invite others before you check out.
Now, you just need to choose who to invite and who pays. This may be a little harder, but we'll do our best to help you through it. ";
        $response['faqs']['ordering_delivery_takeout']['question5']="Can I invite friends who aren't members of Munch Ado to be a part of group orders? ";
        $response['faqs']['ordering_delivery_takeout']['answer5']="NO. Well, yes. You can invite anyone you like via email or text message. They just need to go ahead and create a free Munch Ado profile so they can take advantage of all the great services and content we offer our real friends.";
        $response['faqs']['ordering_delivery_takeout']['question6']="Can I add more people to my group order after the fact?";
        $response['faqs']['ordering_delivery_takeout']['answer6']="I checked with the guys and gals upstairs and they said it's cool. You can add as many people to your order as you like right up until checkout. Anyone who didn't add their meal is automatically dropped from the order when we process your credit card. Harsh, but fair.";
        $response['faqs']['ordering_delivery_takeout']['question7']="Can I track my order?";
        $response['faqs']['ordering_delivery_takeout']['answer7']="Yes. We'll provide you with up-to-date updates from when the kitchen confirms your order to when the delivery man knocks on your door. ";
        $response['faqs']['ordering_delivery_takeout']['question8']="How do I cancel my order?";
        $response['faqs']['ordering_delivery_takeout']['answer8']="Throw your computer in the trash. Pick it up. Clean it off and head over to your order history on your Munch Ado profile page. If you placed your order for immediate delivery, you're out of luck. But, if you wanted it delivered in the future you can cancel it anytime up to one hour before it's scheduled for delivery. Just find the one you'd like to cancel and cancel that sucker. We'll send you an email confirming your cancelation. ";
        $response['faqs']['ordering_delivery_takeout']['question9']="How do I change my future order?";
        $response['faqs']['ordering_delivery_takeout']['answer9']="All you have to do is go to your Munch Ado account page, find the order you'd like to change up, and do what ya gotta do. We'll follow up with an email making sure you wanted to do what ya done did.";
        $response['faqs']['ordering_delivery_takeout']['question10']="What about changing an ASAP order?";
        $response['faqs']['ordering_delivery_takeout']['answer10']="You're SOL. You can try calling the restaurant directly, but no promises. This is why we ask you to confirm this stuff before you order. ";
        $response['faqs']['ordering_delivery_takeout']['question11']="When I submit my order, where does it go?";
        $response['faqs']['ordering_delivery_takeout']['answer11']="Well, there are a series of tubes. No, but really it heads off into the ether where our trained Munch Ado team snatches it out of thin air and makes sure it gets where it's going. Otherwise, it may just float in cyberspace for the rest of eternity. Or until the internet closes its doors.
That's the ideal scenario. Now, if a restaurant hasn't fully integrated itself with Munch Ado (What are they waiting for, a written invitation? Those should be in the mail.) we will call in your order for you and still give you all the conveniences you receive when you order from fully integrated restaurants.";
        $response['faqs']['ordering_delivery_takeout']['question12']="Look, I trust you, but how do I know the restaurant really got my order?";
        $response['faqs']['ordering_delivery_takeout']['answer12']="We'll send you an email immediately after we confirm your order directly with the restaurant. We're for real. ";
        $response['faqs']['ordering_delivery_takeout']['question13']="Okay, that's great, but what if I don't get that email? Do I call you? Them? The Police?";
        $response['faqs']['ordering_delivery_takeout']['answer13']="You can head over to our 24/7 help page where you can chat with our lovely customer service team who are paid to literally solve your first-world problems. ";
        $response['faqs']['ordering_delivery_takeout']['question14']="How do I set up an order to be delivered in the future?";
        $response['faqs']['ordering_delivery_takeout']['answer14']="When you've selected what you'd like to order, you just select when you'd like to have it delivered. ";
        $response['faqs']['ordering_delivery_takeout']['question15']="How far into the future can I order?";
        $response['faqs']['ordering_delivery_takeout']['answer15']="Well, let's just say the foreseeable future. We can't be more specific because someone is always like THE FUTURE IS NOW blah blah. ";
        $response['faqs']['ordering_delivery_takeout']['question16']="How about ordering into the past? I could have really gone for something around 10PM last night.";
        $response['faqs']['ordering_delivery_takeout']['answer16']="We're working on it. Trust us. ";
        $response['faqs']['ordering_delivery_takeout']['question17']="How much are you charging for this service?";
        $response['faqs']['ordering_delivery_takeout']['answer17']="Not a penny. ";
        $response['faqs']['ordering_delivery_takeout']['question18']="Soo... how are you making money off this?";
        $response['faqs']['ordering_delivery_takeout']['answer18']="HOLY SHNIKEES WE FORGOT TO MONETIZE IT. You'll donate right? Send your change to our offices c/o Finance.";
        $response['faqs']['ordering_delivery_takeout']['question19']="Who sets the prices?";
        $response['faqs']['ordering_delivery_takeout']['answer19']="Well, now we wish we did, but all the menu prices come from the restaurants themselves. ";
        $response['faqs']['ordering_delivery_takeout']['question20']="Is there a minimum for delivery?";
        $response['faqs']['ordering_delivery_takeout']['answer20']="Yes and no. There is no site-wide minimum (we're cool like that) but each restaurant does set their own minimum for delivery. There are just some real world realities you can't escape in the digital world.";
        $response['faqs']['ordering_delivery_takeout']['question21']="I'm lazy and don't want to type my credit card info every time. Can you help me out?";
        $response['faqs']['ordering_delivery_takeout']['answer21']="Sure. We'll just store the info in our super secure titanium water/air tight server and only let you access it. ";
        $response['faqs']['ordering_delivery_takeout']['question22']="So, tip wise, what's the etiquette there?";
        $response['faqs']['ordering_delivery_takeout']['answer22']="Well, tips are encouraged but not mandatory. We help calculate the tip as you place items into your cart but we won't force you to include it during checkout. We do hope you'll tip generously though.";
        $response['faqs']['ordering_delivery_takeout']['question23']="Okay, this may seem silly, but can I save multiple delivery addresses?";
        $response['faqs']['ordering_delivery_takeout']['answer23']="Hey, to each their own. And your own can be a whole bunch of delivery addresses. Send food to people. Send it to businesses. Do whatever you want. You pay; it goes.";
        $response['faqs']['ordering_delivery_takeout']['question24']="Are these menus and prices verified and updated?";
        $response['faqs']['ordering_delivery_takeout']['answer24']="They should be. We have a whole team taking care of this. We're talking All-Star team. You're welcome to join the team and report errors to WeCare@MunchAdo.com. We'll even compensate you for your work with anywhere from 50 to 500 points.";
        $response['faqs']['ordering_delivery_takeout']['question25']="What if the restaurant has changed its price or updated the menu? Is there a special division of the police who handles these things?";
        $response['faqs']['ordering_delivery_takeout']['answer25']="We JUST talked about this. Everything is up to date, and if it isn't we'll have a pretty frank and direct conversation with the restaurant to make sure it never happens again. You just have to slip their name under into email's inbox, WeCare@MunchAdo.com, in the middle of the night.";
        $response['faqs']['ordering_delivery_takeout']['question26']="If I find the restaurant displayed incorrect information that's not close to reality, what can I do about it? Can I fight you?. Bare-knuckle. No Holds Barred. 3 Count Pin.";
        $response['faqs']['ordering_delivery_takeout']['answer26']="You better get in line because if there is anything wrong, we'll be going through some rough and tumble with the restaurant ourselves. We're eaters not fighters though, so we do everything we can to make sure you, us, and them are all on the same page. A clean, white, and bloodless page.";
        $response['faqs']['ordering_delivery_takeout']['question27']="I only eat 3 different meals a week. You store that info so I can reorder and speed things up right?";
        $response['faqs']['ordering_delivery_takeout']['answer27']="Sure! You can eat at as many or few places you like and we'll store all of that information on your Munch Ado page where you can quickly access it and re-order your favorite meals. We're on top of it.";
        $response['faqs']['ordering_delivery_takeout']['question28']="If the food I get isn't the food I ordered or they didn't listen to my super special instructions, who do I call to complain? You? Them? Ghostbusters?";
        $response['faqs']['ordering_delivery_takeout']['answer28']="You only call the Ghostbusters when you have ghosts to bust and happen to live in their cinematic universe. You can call us, email us or chat with us directly 24/7 by heading over to the help page. It, and we, are here to help.";
        $response['faqs']['ordering_delivery_takeout']['question29']="I'm a sharer and want to tell the world about what I ate, am eating or are about to eat. How do I do such a thing? Can I also rate aspects of my meal? 'Cause, I'd be all over that.";
        $response['faqs']['ordering_delivery_takeout']['answer29']="You can absolutely rate and review any meal you book or order through Munch Ado. All you have to do is head over to your Munch Ado page, select the transaction you'd like to review and go to town. You can even upload pictures of your food and the restaurant if you're more of a visual expressionist.";
        $response['faqs']['ordering_delivery_takeout']['question30']="When I order in a group, can each participant pay for their share?";
        $response['faqs']['ordering_delivery_takeout']['answer30']="Yes and no. You can designate one person \"loan shark\" and have them track down their money. OR you can designate them the patron saint of food and have them pay for the whole meal. ";
        $response['faqs']['ordering_delivery_takeout']['question31']="If I find the restaurant displayed incorrect information that's not close to reality, what I can do about it? Can I fight you? 3 Rounds. Bare-knuckle. No Holds Barred. 3 Count Pin.";
        $response['faqs']['ordering_delivery_takeout']['answer31']="You better get in line because if there is anything wrong, we'll be going through some rough and tumble with the restaurant ourselves. We're eaters not fighters though so we do everything we can to make sure you, us, and them are all on the same page. A clean., white, bloodless page.";
        $response['faqs']['ordering_delivery_takeout']['question32']="Can I order from multiple restaurants in a single order?";
        $response['faqs']['ordering_delivery_takeout']['answer32']="No. How would that even work? It would be madness... or brilliance. This sounds like a Phase 5 idea!";
        $response['faqs']['ordering_delivery_takeout']['question33']="For takeout, do I have to call ahead? I usually have to call ahead. ";
        $response['faqs']['ordering_delivery_takeout']['answer33']="You do not have to call ahead. In fact, you never have to pick up the phone again after you join Munch Ado. But you should if your parents call. They do worry. Munch Ado on the other hand supports your digital lifestyle and helps you order everything, even takeout, with a few clicks and only as much typing as necessary.";
        
        $response['faqs']['table_reservation']['title']="TABLES RESERVATIONS";
        $response['faqs']['table_reservation']['question1']="So this whole reservation \"thing\" how does it work?";
        $response['faqs']['table_reservation']['answer1']="Well you reserve a spot at a restaurant through Munch Ado and then you go to the restaurant and eat. Pretty simple right? WRONG! We check and recheck to make sure the restaurant is open, accepting tables and has your reservation reserved. It's a lot of work, but you're worth it. ";
        $response['faqs']['table_reservation']['question2']="How do I cancel my reservation?";
        $response['faqs']['table_reservation']['answer2']="Throw your computer in the trash. Pick it up. Clean it off and head over to your Munch Ado profile page and your order. Find the one you'd like to cancel and cancel that sucker. We'll send you an email confirming your cancelation.";
        $response['faqs']['table_reservation']['question3']="How do I change my reservation?";
        $response['faqs']['table_reservation']['answer3']="All you have to do is go to your Munch Ado account page, find the reservation you'd like to change up, and do what ya gotta do. We'll follow up with an email making sure you wanted to do what ya done did.";
        $response['faqs']['table_reservation']['question4']="Do I have to call and confirm the changes?";
        $response['faqs']['table_reservation']['answer4']="Nope, we'll actually email you to confirm YOUR changes. Pretty cool right? Right.";
        $response['faqs']['table_reservation']['question5']="What if my reservation gets rejected?";
        $response['faqs']['table_reservation']['answer5']="Well, it's clearly the restaurant's lost. We'll help you find a host of other restaurants ready to cater to your every whim so you'll never be hurt like that again. *hugs*";
        $response['faqs']['table_reservation']['question6']="Are these menus and prices verified and updated? 'Cause, that's a deal breaker.";
        $response['faqs']['table_reservation']['answer6']="They should be. We have a whole team taking care of this. We're talking All-Star team big. You're welcome to join the team and report errors to WeCare@MunchAdo.com. We'll even compensate your work with anywhere from 50 to 500 points";
        $response['faqs']['table_reservation']['question7']="What if the restaurant has changed its price or updated the menu? Is there a special division of the police who handles these things?";
        $response['faqs']['table_reservation']['answer7']="We JUST talked about this. Everything is up to date, and if it isn't we'll have a pretty frank and direct conversation with the restaurant to make sure it never happens again. You just have to slip their name into our email's inbox, WeCare@MunchAdo.com in the middle of the night.";
        $response['faqs']['table_reservation']['question8']="I only eat at 3 places. You store that info so I can reserve tables quickly right? ";
        $response['faqs']['table_reservation']['answer8']="Sure! You can eat at as many or few places you like and we'll store all of that information on your Munch Ado page. We're on top of it.";
        $response['faqs']['table_reservation']['question9']="I'm a sharer and want to tell the world about what I ate, am eating or are about to eat. How do I do such a thing? Can I also rate aspects of my meal? 'Cause, I'd be all over that.";
        $response['faqs']['table_reservation']['answer9']="You can absolutely rate and review any meal you book or order through Munch Ado. All you have to do is head over to your Munch Ado page, select the transaction you'd like to review and go to town. You can even upload pictures of your food and the restaurant if you're more of a visual expressionist.";
        $response['faqs']['table_reservation']['question10']="How long does Munch Ado take to confirm reservations? ‘Cause, I'm a busy person.";
        $response['faqs']['table_reservation']['answer10']="We'll instantly confirm your reservation and then remind you about the reservation to confirm you remember it. Pretty sweet right? Right.";
        $response['faqs']['table_reservation']['question11']="If my reservation is modified or cancelled, will you tell the invitees automatically? I don't like confrontation.";
        $response['faqs']['table_reservation']['answer11']="No problem. We're perfectly happy to shut it down. We can see their faces now.";
        $response['faqs']['table_reservation']['question12']="If I find the restaurant displayed incorrect information that's not close to reality, what can I do about it? Can I fight you? Bare-knuckle. No Holds Barred. 3 Count Pin.";
        $response['faqs']['table_reservation']['answer12']="You better get in line because if there is anything wrong, we'll be going through some rough and tumble with the restaurant ourselves. We're eaters not fighters though so we do everything we can to make sure you, us, and them are all on the same page. A clean, white and bloodless page.";
        
        $response['faqs']['buy_deals_coupons']['title']="BUYING DEALS OR COUPONS";
        $response['faqs']['buy_deals_coupons']['question1']="Advertising has confused me. What's the difference between a deal and a coupon?";
        $response['faqs']['buy_deals_coupons']['answer1']="It's surprisingly simple. Deals you buy upfront while coupons can be \"clipped\" at no cost and used when you dine in. See the difference? Okay good case we were kind of squinting there for a second.";
        $response['faqs']['buy_deals_coupons']['question2']="Can I buy a deal as a gift or clip a coupon for someone else? ";
        $response['faqs']['buy_deals_coupons']['answer2']="Maybe someday, but not today. You're welcome to link your friends to deals you think they may be interested in, but they're going to have to put their money where your mouth is to chow down on the savings.
Who knows, soon there may be Munch Ado gift cards hanging alongside pre-paid debit cards in gas stations and convince stores across the nation.";
        $response['faqs']['buy_deals_coupons']['question3']="Can I combine coupons and deals like some sort of super savings superhero?";
        $response['faqs']['buy_deals_coupons']['answer3']="No, although our deals and coupons are made up of sugar, spice and everything nice, theyâ€™re only stable when everything is in proper balance. Combining them could be catastrophic.";
        $response['faqs']['buy_deals_coupons']['question4']="Do Coupons or Deals expire?";
        $response['faqs']['buy_deals_coupons']['answer4']="Yes they're not Twinkies. Duh. But don't worry, deals and coupons, like milk, have their expiration date clearly marked. Please mind the date; we wouldn't want to spoil anyone's good time. (Do you see what we did there?)";
        
        $response['faqs']['payments']['title']="PAYMENTS";
        $response['faqs']['payments']['question1']="Do you accept Monopoly money? ";
        $response['faqs']['payments']['answer1']="As a currency? Not at the moment. We wouldn't mind finding a fresh new stack of oranges hidden under the board during our weekly game night though.";
        $response['faqs']['payments']['question2']="So... my credit card and personal information... who do you share that with?";
        $response['faqs']['payments']['answer2']="No body. No one. No thing. Really, no nouns, verbs, adjectives, adverbs, or anything else. We personally charge your credit card and never share it, or its number with the restaurants.
From what I've been told, all of your information is actually stored away in an impenetrable fortress/castle type place deep in a forgotten jungle surround by a moat as wide as the mighty Mississippi river at its mightiest. The castle is manned by a swarm of ninjas and has automated death lasers and stuff. That's what the IT guys told me anyway. It may have been a metaphor.";
        $response['faqs']['payments']['question3']="Why no love for COD (Cash on Delivery)? ";
        $response['faqs']['payments']['answer3']="We love COD, our finance guys though... they're sticklers. Sorry everyone.";
        $response['faqs']['payments']['question4']="I'm lazy and don't want to type my credit card info every time. Can you help me out?";
        $response['faqs']['payments']['answer4']="Sure. We'll just store the info in our super secure titanium water/air tight server and only let you access it.";
        $response['faqs']['payments']['question5']="So, tip wise, what's the etiquette there?";
        $response['faqs']['payments']['answer5']="Well, tips are encouraged but not mandatory. We help calculate the tip as you place items into your cart but we won't force you to include it during checkout. We do hope you'll tip generously though.";
        $response['faqs']['payments']['question6']="What if the restaurant has changed its price or updated the menu? Is there a special division of the police who handles these things?";
        $response['faqs']['payments']['answer6']="We talked about this. Everything is up to date, and if it isn't we'll have a pretty frank and direct conversation with the restaurant to make sure it never happens again. You just have to slip their name into our email's inbox AdoNot@MunchAdo.com.";
        $response['faqs']['payments']['question7']="When I order in a group, can each participant pay for their share?";
        $response['faqs']['payments']['answer7']="Yes and no. You can designate one person \"loan shark\" and have them track down their money. OR you can designate them the patron saint of food where they pay for the whole meal.";
        $response['faqs']['payments']['question8']="Can I combine coupons and deals like some sort of super savings superhero?";
        $response['faqs']['payments']['answer8']="No, although our deals and coupons are made up of sugar, spice and everything nice, they're only stable when everything is in proper balance. Combining them could be catastrophic.";
        
        $response['faqs']['refunds_cancellations']['title']="REFUNDS & CANCELLATIONS";
        $response['faqs']['refunds_cancellations']['question1']="I ate my food but I don't want it anymore, can I have a refund?";
        $response['faqs']['refunds_cancellations']['answer1']="We never like to rule out something completely, but no. Absolutely not.";
        $response['faqs']['refunds_cancellations']['question2']="How about a refund after I've canceled my order?";
        $response['faqs']['refunds_cancellations']['answer2']="Well, that's a little more reasonable and totally doable. You have up until one hour before your order is scheduled for delivery to cancel or modify your order. Hey, it's better than those \"delicate geniuses\" who require 24 hour notice for all cancellations. I mean, come on. Get over yourselves.";
        $response['faqs']['refunds_cancellations']['question3']="So how do I cancel my orders and reservations?";
        $response['faqs']['refunds_cancellations']['answer3']="We covered this elsewhere, but because you came to this section, we figure you REALLY want to know. Head over to your Munch Ado page and choose the order or reservation you'd like to cancel. You can cancel or modify your order right up until an hour before it's set to be delivered or your table is set.";
        
        $response['faqs']['password']['title']="PASSWORD";
        $response['faqs']['password']['question1']="I can't remember my login information or my password. Whose fault is this?";
        $response['faqs']['password']['answer1']="Clearly it's our fault. Just head over to your Munch Ado page and we'll set you up with a brand spanking new one via a series of clicks and emails.";
        $response['faqs']['password']['question2']="I forgot how to type on a keyboard, I don't know how I got here... can you help with that too?";
        $response['faqs']['password']['answer2']="Sorry friend, you're on your own. Kudos on finding your way to the FAQs though.";
        
        $response['faqs']['email_notification']['title']="EMAIL NOTIFICATIONS";
        $response['faqs']['email_notification']['question1']="No offense, but I don't want to receive email notifications for every single EFFIN' transaction. I'm an adult. Can I change my email preferences?";
        $response['faqs']['email_notification']['answer1']="Sure, if you want us to love you less. We can dial it down; just let us know which emails you'd like to receive in your Munch Ado account preferences. Just don't come running to us when you forget to add fresh milk to your order.";
        $response['faqs']['email_notification']['question2']="How can I change my email address?";
        $response['faqs']['email_notification']['answer2']="It's a 2 step process. Step 1: Munch Ado-it-yourself by choosing which email provider and address you'd like to spend the rest of your life with. It's a big commitment, so we hope you're ready. Step 2: Head over to your Munch Ado page and update your info. Quick, easy and painless. ";
        $response['faqs']['email_notification']['question3']="Emails are going to my junk folder. Is this a sign? Can I fix this?";
        $response['faqs']['email_notification']['answer3']="It may be a sign... to change your email provider, but, you can always just mark us as A-Okay and \"Not Junk\" to make sure all of our communiques find their way to your inbox.";
        $response['faqs']['email_notification']['question4']="You've answered all my questions. What do I do now?";
        $response['faqs']['email_notification']['answer4']="You eat. You eat to your heart's content as you watch your friends eat their hearts out.";

        return $response;
    }
}
	