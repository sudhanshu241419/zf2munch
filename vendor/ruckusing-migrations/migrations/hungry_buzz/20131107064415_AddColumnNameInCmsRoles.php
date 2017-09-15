<?php

class AddColumnNameInCmsRoles extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("UPDATE `cms_users` SET `role_id` = '1' WHERE `id` = '1';");
    	$this->execute("DROP TABLE `cms_roles`;");
    	$this->execute("DROP TABLE `cms_roles_access`;");

		$this->execute("CREATE TABLE IF NOT EXISTS `cms_roles` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `role` varchar(50) CHARACTER SET latin1 NOT NULL,
		  `status` tinyint(1) NOT NULL DEFAULT '1',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;
		");
		$this->execute("INSERT INTO `cms_roles` (`id`, `role`, `status`) VALUES	(1, 'Super Admin', 1);");

		$this->execute("CREATE TABLE IF NOT EXISTS `cms_roles_access` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `role_id` int(11) NOT NULL,
		  `module_id` int(11) NOT NULL,
		  `module_create` tinyint(1) NOT NULL DEFAULT '0',
		  `module_update` tinyint(1) NOT NULL DEFAULT '0',
		  `module_view` tinyint(1) NOT NULL DEFAULT '0',
		  `module_delete` tinyint(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=38 ;");

		$this->execute("INSERT INTO `cms_roles_access` (`id`, `role_id`, `module_id`, `module_create`, `module_update`, `module_view`, `module_delete`) VALUES
			(1, 1, 14, 1, 1, 1, 1),
			(2, 1, 12, 1, 1, 1, 1),
			(3, 1, 15, 1, 1, 1, 1),
			(4, 1, 23, 1, 1, 1, 1),
			(5, 1, 25, 1, 1, 1, 1),
			(6, 1, 26, 1, 1, 1, 1),
			(7, 1, 27, 1, 1, 1, 1),
			(8, 1, 28, 1, 1, 1, 1),
			(9, 1, 29, 1, 1, 1, 1),
			(10, 1, 30, 1, 1, 1, 1),
			(11, 1, 32, 1, 1, 1, 1),
			(12, 1, 33, 1, 1, 1, 1),
			(13, 1, 34, 1, 1, 1, 1),
			(14, 1, 36, 1, 1, 1, 1),
			(15, 1, 37, 1, 1, 1, 1),
			(16, 1, 38, 1, 1, 1, 1),
			(17, 1, 39, 1, 1, 1, 1),
			(18, 1, 40, 1, 1, 1, 1),
			(19, 1, 42, 1, 1, 1, 1),
			(20, 1, 43, 1, 1, 1, 1),
			(21, 1, 17, 1, 1, 1, 1),
			(22, 1, 18, 1, 1, 1, 1),
			(23, 1, 19, 1, 1, 1, 1),
			(24, 1, 20, 1, 1, 1, 1),
			(25, 1, 21, 1, 1, 1, 1),
			(26, 1, 8, 1, 1, 1, 1),
			(27, 1, 9, 1, 1, 1, 1),
			(28, 1, 45, 1, 1, 1, 1),
			(29, 1, 46, 1, 1, 1, 1),
			(30, 1, 50, 1, 1, 1, 1),
			(31, 1, 51, 1, 1, 1, 1),
			(32, 1, 52, 1, 1, 1, 1),
			(33, 1, 53, 1, 1, 1, 1),
			(34, 1, 54, 1, 1, 1, 1),
			(35, 1, 55, 1, 1, 1, 1),
			(36, 1, 56, 1, 1, 1, 1),
			(37, 1, 57, 1, 1, 1, 1);");
    }//up()

    public function down()
    {
    }//down()
}
