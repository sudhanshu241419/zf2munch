<?php

class InsertPointSourceDetailData extends Ruckusing_Migration_Base
{
    public function up()
    {
    $this->execute("INSERT INTO `point_source_detail` (`id`, `name`, `points`, `csskey`, `identifier`, `created_at`, `points_for`, `dindex`, `dstatus`) VALUES
    (1, 'Order placed/Takeout', 20, 'i_order active', 'orderConfirmed', '2013-06-14 06:11:52', 'bt', 1, '1'),
    (2, 'Group order placed', 20, 'groupIconPoint active', 'groupOrderPlaced', '2013-06-14 06:11:52', 'ws', 24, '0'),
    (3, 'Reserve a table', 10, 'i_reserve_a_table', 'reserveTable', '2013-06-14 06:11:52', 'bt', 2, '1'),
    (4, 'Purchase a Deal/Coupon', 5, 'dealIconPoint active', 'purchaseDealCoupon', '2013-06-14 06:11:52', 'ws', 28, '0'),
    (5, 'Friend accepts your invitation', 15, 'i_invitefriends', 'madeFriend', '2013-06-14 06:11:52', 'ws', 5, '1'),
    (6, 'Review a restaurant', 5, 'i_ratereview', 'i_ratereview', '2013-06-14 06:18:17', 'ws', 7, '1'),
    (7, 'Upload pictures from your Munch Ado experiences', 10, 'i_postpictures', 'imageApproved', '2013-06-14 06:18:17', 'ws', 14, '1'),
    (8, 'Email us about a major bug on MunchAdo.com', 5, '', 'reportError', '2013-06-14 06:18:17', 'ws', 12, '1'),
    (9, 'Complete profile', 50, 'i_completeprofile', 'completeProfile', '2013-06-14 06:18:17', 'ws', 25, '0'),
    (10, 'Post on Facebook', 10, 'facebookIconPoint', 'postOnFacebook', '2013-06-14 06:18:17', 'ws', 26, '0'),
    (11, 'Post on Twitter', 10, 'twitterIconPoint', 'postOnTwitter', '2013-06-14 06:19:22', 'ws', 27, '0'),
    (12, 'Register via email', 100, 'i_completeprofile', 'normalRegister', '2014-04-14 00:00:00', 'ws', 3, '1'),
    (13, 'Register with a social account', 100, 'i_completeprofile', 'socialRegister', '2014-04-14 00:00:00', 'ws', 4, '1'),
    (14, 'Mark a food item as loved it', 1, 'loveIcon active', 'loveFood', '2014-04-14 00:00:00', 'ws', 10, '1'),
    (15, 'Add a food item to your crave list', 1, 'starIcon active', 'craveFood', '2014-04-14 00:00:00', 'ws', 9, '1'),
    (16, 'Mark a restaurant as loved it', 1, 'loveIcon active', 'loveRestaurant', '2014-04-16 00:00:00', 'ws', 11, '1'),
    (17, 'Friend joins your reservation', 5, 'i_reserve_a_table active', 'acceptReservation', '2014-04-17 00:00:00', 'ws', 6, '1'),
    (18, 'Facebook Share', 3, 'i_facebookshare', 'facebookShare', '2015-03-19 00:00:00', 'ws', 19, '0'),
    (19, 'Twitter Share', 3, 'i_twittershare', 'twitterShare', '2015-03-19 00:00:00', 'ws', 21, '0'),
    (20, 'Mark as been there', 1, 'i_beenthererestaurant', 'beenthererestaurant', '2015-03-19 00:00:00', 'ws', 13, '1'),
    (21, 'Add a restaurant to your crave list', 1, 'i_craveitrestaurant', 'craveitrestaurant', '2015-03-19 00:00:00', 'ws', 8, '1'),
    (22, 'Try Food', 1, 'tryIcon active', 'tryFood', '2014-04-14 00:00:00', 'ws', 22, '0'),
    (23, 'Upload Restaurant Photo', 50, 'twitterIconPoint', 'uploadRestaurantPhoto', '2013-06-14 06:19:22', 'ws', 23, '0'),
    (24, 'Check In', 2, 'checkIn', 'checkIn', '2013-06-14 06:19:22', 'ap', 4, '1'),
    (25, 'Check in with menu item', 3, 'checkMenu', 'checkMenu', '2013-06-14 06:19:22', 'ap', 6, '1'),
    (26, 'Check in with photo', 5, 'checkPhoto', 'checkPhoto', '2013-06-14 06:19:22', 'ap', 7, '1'),
    (27, 'Check in with friend', 3, 'checkFriend', 'checkFriend', '2013-06-14 06:19:22', 'ap', 8, '1'),
    (28, 'Check in with menu and photo', 7, 'checkMenuPhoto', 'checkMenuPhoto', '2013-06-14 06:19:22', 'ap', 15, '1'),
    (29, 'Check in with menu and friend', 7, 'checkMenuFriend', 'checkMenuFriend', '2013-06-14 06:19:22', 'ap', 16, '1'),
    (30, 'Check in with menu, photo and friend.', 7, 'checkMenuPhotoFriend', 'checkMenuPhotoFriend', '2013-06-14 06:19:22', 'ap', 17, '1'),
    (31, 'Registrations with edu', 400, 'i_completeprofile', 'eduRegister', '2014-04-14 00:00:00', 'bt', 23, '0'),
    (32, 'Leave a tip at restaurants you\'\ve tried.', 3, 'leaveTip', 'leaveTip', '2015-09-03 06:19:22', 'ap', 3, '1'),
    (33, 'Let us know about secret menu items on the down low', 25, 'i_right_rightMark', 'i_right_rightMark', '2015-09-04 00:00:00', 'ws', 15, '1'),
    (34, 'Let us know about a missing menu item', 10, 'i_right_rightMark', 'i_right_rightMark', '2015-09-04 06:00:00', 'ws', 16, '1'),
    (35, 'Email us about new, up-and-coming local chefs', 100, 'i_right_rightMark', 'i_right_rightMark', '2015-09-04 00:00:00', 'ws', 17, '1'),
    (36, 'Correct a major mistake on MunchAdo.com', 100, 'i_right_rightMark', 'i_right_rightMark', '2015-09-04 06:00:00', 'ws', 18, '1'),
    (37, 'Tell us everything about a local restaurant we don\'\t know', 250, 'i_right_rightMark', 'i_right_rightMark', '2015-09-04 00:00:00', 'ws', 20, '1'),
    (38, 'Unlock Munchers', 25, 'i_unlock_munchers rightMark', 'i_unlock_munchers', '2015-10-06 10:35:25', 'ap', 19, '1'),
    (39, 'Check in with tip', 3, 'checkTip', 'checkTip', '2015-10-06 07:19:32', 'ap', 5, '1'),
    (40, 'Check in with tip and menu item', 5, 'checkTipMenu', 'checkTipMenu', '2015-10-06 08:33:22', 'ap', 9, '1'),
    (41, 'Check in with tip and photo', 5, 'CheckTipPhoto', 'CheckTipPhoto', '2015-10-06 07:19:32', 'ap', 10, '1'),
    (42, 'Check in with tip and friend', 5, 'CheckTipFriend', 'CheckTipFriend', '2015-10-06 07:19:32', 'ap', 11, '1'),
    (43, 'Check in with tip, menu and photo', 7, 'CheckTipMenuPhoto', 'CheckTipMenuPhoto', '2015-10-06 07:19:32', 'ap', 12, '1'),
    (44, 'Check in with tip, menu and friend', 7, 'CheckTipMenuFriend', 'CheckTipMenuFriend', '2015-10-06 07:19:32', 'ap', 13, '1'),
    (45, 'Check in with tip, photo and friend', 7, 'CheckTipPhotoFriend', 'CheckTipPhotoFriend', '2015-10-06 07:19:32', 'ap', 14, '1'),
    (46, 'Check in with tip, menu, photo and friend', 10, 'CheckTipMenuPhotoFriend', 'CheckTipMenuPhotoFriend', '2015-10-06 07:19:32', 'ap', 18, '1')");
    }//up()

    public function down()
    {
    }//down()
}
