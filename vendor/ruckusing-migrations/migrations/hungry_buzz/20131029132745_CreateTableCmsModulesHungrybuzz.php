<?php

class CreateTableCmsModulesHungrybuzz extends Ruckusing_Migration_Base
{
  public function up()
    {  
    	/*$this->drop_table("cms_modules");
    	$this->execute("CREATE TABLE IF NOT EXISTS `cms_modules`(
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`pid` int(11) NOT NULL,
						`name` varchar(50) NOT NULL,
						`title` varchar(500) NOT NULL,
						`link` varchar(500) NOT NULL,
						`sorting_order` int(2) NOT NULL,
						`status` tinyint(1) NOT NULL DEFAULT '1',
						PRIMARY KEY (`id`)
						) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
    	
    	$this->execute("INSERT INTO `cms_modules` (`id`, `pid`, `name`, `title`, `link`, `sorting_order`, `status`) VALUES
						(7, 0, 'Content upload', '', '', 5, 1),
						(8, 7, 'Restaurants', 'Restaurants Upload', 'Injection', 1, 1),
						(9, 7, 'Update Restaurant Features', 'Update Restaurant Features', 'UpdateRestaurant', 2, 1),
						(11, 0, 'Crm management', '', '', 0, 1),
						(12, 11, 'Reservation', '', 'userReservation', 2, 1),
						(14, 11, 'Order', 'User Order', 'userOrder', 1, 1),
						(15, 11, 'Dashboard', 'Dashboard', 'dashboardUsers', 3, 1),
						(16, 0, 'Admin Management', 'Admin Management', '', 4, 1),
						(17, 16, 'Module', 'Module', 'moduleManagement', 0, 1),
						(18, 16, 'Role', 'Role Management', 'roleManagement', 0, 1),
						(19, 16, 'User Role', 'User Role Management', 'userRoleModule', 0, 1),
						(20, 16, 'Users Manage', 'Users Manage', '#', 0, 1),
						(21, 16, 'Log Management', 'Log Management', '#', 0, 1),
						(22, 0, 'Content Master', 'Content Master', '', 1, 1),
						(23, 22, 'Addons', 'Addons', 'addons', 1, 1),
						(25, 22, 'Features', 'Features', 'featuresManage', 2, 1),
						(26, 22, 'Cuisines', 'Cuisines', 'cuisinesManage', 3, 1),
						(27, 22, 'Cities', 'Cities', 'cities', 4, 1),
						(28, 22, 'States', 'States', 'states', 5, 1),
						(29, 22, 'Country', 'Country', 'Countries', 6, 1),
						(30, 22, 'Gallery', 'Gallery', 'restaurantImages', 7, 1),
						(31, 0, 'Restaurant Management', 'Restaurant Management', '', 2, 1),
						(32, 31, 'Restaurants', 'Restaurants', 'restaurantManage', 1, 1),
						(33, 31, 'Features', 'Restaurant Features', 'restaurantFeatures/index?stateset=35&amp;cityid=235&amp;restaurantid=3&amp;menutext=&amp;yt0=Search', 2, 1),
						(34, 31, 'Calendars', 'Restaurant Calendars', 'restaurantCalendars/index?stateset=35&amp;cityid=235&amp;restaurantid=3&amp;menutext=&amp;yt0=Search', 3, 1),
						(36, 31, 'Menu', 'Menu Manage', 'menuManage/index?stateset=35&amp;cityid=235&amp;restaurantid=3&amp;menutext=&amp;yt0=Search', 4, 1),
						(37, 31, 'Menu Addons', 'Menu Addons', 'menuAddons', 5, 1),
						(38, 31, 'Addons Settings', 'Addons Settings', 'Addons', 6, 1),
						(39, 31, 'Stories', 'Restaurant Stories', 'restaurantStories', 7, 1),
						(40, 31, 'Deals Coupons', 'Deals Coupons', 'dealsCoupons', 8, 1),
						(41, 0, 'Reviews Management', 'Reviews Management', '', 3, 1),
						(42, 41, 'Users Manage', 'Users Manage', 'usersManage', 1, 1),
						(43, 41, 'Reviews', 'Reviews Management', 'usersReview', 2, 1),
						(45, 7, 'Menues', 'Menues Upload', 'Injection/menu', 3, 1),
						(46, 7, 'Blogs', 'Blogs Upload', 'Injectionblog', 4, 1),
						(50, 7, 'Reviews', 'Reviews Upload', 'Injectionreview', 6, 1),
						(51, 7, 'Images', 'Images Upload', 'Resizeimg', 7, 1),
						(52, 7, 'Social Media Feature', 'Social Media Feature', 'SocialFeature', 8, 1),
						(53, 7, 'Menu Cuisine Map', 'Menu Cuisine Map', 'Injection/MenuCuisine', 9, 1),
						(54, 7, 'Restaurant Story', 'Restaurant Story Upload', 'RestaurantStory', 10, 1),
						(55, 7, 'Story Images', 'Story Images Upload', 'RestaurantStory/ImageIndex', 11, 1),
						(56, 7, 'Restaurant parameters', 'Restaurant parameters Upload', 'RestaurantParameters', 12, 1),
						(57, 7, 'Cuisine Master', 'Cuisine Master Upload', 'CuisineMaster', 13, 1);");
						*/}//up()

    public function down()
    {
    }//down()
}
