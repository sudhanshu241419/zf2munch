<?php

class ChangeInCmsModule extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("DROP TABLE `cms_modules`;");

    	$this->execute("CREATE TABLE IF NOT EXISTS `cms_modules` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `pid` int(11) NOT NULL,
			  `name` varchar(50) NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `link` varchar(255) NOT NULL,
			  `icon` varchar(255) NOT NULL,
			  `sorting_order` int(2) NOT NULL,
			  `status` tinyint(1) NOT NULL DEFAULT '1',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=59 ;");

    	$this->execute("INSERT INTO `cms_modules` (`id`, `pid`, `name`, `title`, `link`, `icon`, `sorting_order`, `status`) VALUES
			(7, 0, 'Content upload', 'Content upload', '', '', 5, 1),
			(8, 7, 'Restaurants', 'Restaurants', 'Injection/index', 'edit', 1, 1),
			(9, 7, 'Update Restaurant Features', 'Update Restaurant Features', 'UpdateRestaurant/index', 'eye-open', 2, 1),
			(11, 0, 'Crm management', 'Crm management', '', '', 0, 1),
			(12, 11, 'Reservation', 'Reservation', 'userReservation/index', 'screenshot', 2, 1),
			(14, 11, 'Order', 'Order', 'userOrder/index', 'star-empty', 1, 1),
			(15, 11, 'Dashboard', 'Dashboard', 'dashboardUsers/index', 'home', 3, 1),
			(16, 0, 'Admin Management', 'Admin Management', '', '', 4, 1),
			(17, 16, 'Module', 'Module', 'moduleManagement/index', 'star', 0, 1),
			(18, 16, 'Role', 'Role ', 'roleManagement/index', 'film', 0, 1),
			(19, 16, 'User Role', 'User Role', 'userRoleModule/index', 'user', 0, 1),
			(20, 16, 'CMS Users', 'CMS Users', 'cmsUsers/index', 'user', 3, 1),
			(21, 16, 'Log Management', 'Log Management', 'loguserManage/index', 'list-alt', 5, 1),
			(22, 7, 'Content Master', 'Content Master', '', 'thumbs-up', 1, 1),
			(23, 22, 'Addons', 'Addons', 'addons/index', 'align-left', 1, 1),
			(25, 22, 'Features', 'Features', 'featuresManage/index', 'eye-open', 2, 1),
			(26, 22, 'Cuisines', 'Cuisines', 'cuisinesManage/index', 'th-large', 3, 1),
			(27, 22, 'Cities', 'Cities', 'cities/index', 'bell', 4, 1),
			(28, 22, 'States', 'States', 'states/index', 'cog', 5, 1),
			(29, 22, 'Country', 'Country', 'countries/index', 'flag', 6, 1),
			(30, 22, 'Gallery', 'Gallery', 'restaurantImages/index', 'picture', 7, 1),
			(31, 0, 'Restaurant Management', 'Restaurant Management', '', '', 2, 1),
			(32, 31, 'Restaurants', 'Restaurants', 'restaurantManage/index', 'edit', 1, 1),
			(33, 31, 'Features', 'Features', 'restaurantFeatures/index?stateset=35&amp;cityid=235&amp;restaurantid=3&amp;menutext=&amp;yt0=Search', 'eye-open', 2, 1),
			(34, 31, 'Calendars', 'Calendars', 'restaurantCalendars/index?stateset=35&amp;cityid=235&amp;restaurantid=3&amp;menutext=&amp;yt0=Search', 'calendar', 3, 1),
			(36, 31, 'Menu', 'Menu ', 'menuManage/index?stateset=35&amp;cityid=235&amp;restaurantid=3&amp;menutext=&amp;yt0=Search', 'globe', 4, 1),
			(37, 31, 'Menu Addons', 'Menu Addons', 'menuAddons/index', 'th', 5, 1),
			(38, 31, 'Addons Settings', 'Addons Settings', 'Addons/index', 'th-list', 6, 0),
			(39, 31, 'Stories', 'Stories', 'restaurantStories/index', 'share', 7, 1),
			(40, 31, 'Deals Coupons', 'Deals Coupons', 'dealsCoupons/index', 'tags', 8, 1),
			(41, 0, 'Reviews Management', 'Reviews Management', '', 'thumbs-up', 3, 1),
			(42, 41, 'Users Manage', 'Users Manage', 'usersManage/index', 'user', 1, 1),
			(43, 41, 'Reviews', 'Reviews ', 'usersReview/index', 'thumbs-up', 2, 1),
			(45, 7, 'Menues', 'Menues ', 'Injection/menu/index', 'globe', 3, 1),
			(46, 7, 'Blogs', 'Blogs ', 'Injectionblog/index', 'book', 4, 1),
			(50, 7, 'Reviews', 'Reviews ', 'Injectionreview/index', 'thumbs-up', 6, 1),
			(51, 7, 'Images', 'Images', 'Resizeimg/index', 'picture', 7, 1),
			(52, 7, 'Social Media Feature', 'Social Media Feature', 'SocialFeature/index', 'share', 8, 1),
			(53, 7, 'Menu Cuisine Map', 'Menu Cuisine Map', 'Injection/MenuCuisine/index', 'globe', 9, 1),
			(54, 7, 'Restaurant Story', 'Restaurant Story ', 'RestaurantStory/index', 'eye-open', 10, 1),
			(55, 7, 'Story Images', 'Story Images', 'RestaurantStory/ImageIndex', 'picture', 11, 1),
			(56, 7, 'Restaurant parameters', 'Restaurant parameters ', 'RestaurantParameters/index', 'upload', 12, 1),
			(57, 7, 'Cuisine Master', 'Cuisine Master ', 'CuisineMaster/index', 'thumbs-up', 13, 1),
			(58, 11, 'Restaurants Config', 'Restaurants Config', 'restaurantsConfig/index', 'star', 4, 1);");
    }//up()

    public function down()
    {
    }//down()
}
