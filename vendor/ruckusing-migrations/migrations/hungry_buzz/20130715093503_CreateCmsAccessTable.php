<?php

class CreateCmsAccessTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute('CREATE TABLE IF NOT EXISTS `cms_modules` (
  		`id` int(11) NOT NULL AUTO_INCREMENT,
 		 `name` varchar(50) NOT NULL,
 		 PRIMARY KEY (`id`)
		)');
		$this->execute('CREATE TABLE IF NOT EXISTS `cms_roles` (
  		`id` int(11) NOT NULL AUTO_INCREMENT,
  		`role` varchar(50) NOT NULL,
		  PRIMARY KEY (`id`)
		)');
		$this->execute('CREATE TABLE IF NOT EXISTS `cms_roles_access` (
  		`id` int(11) NOT NULL AUTO_INCREMENT,
  		`role_id` int(11) NOT NULL,
 		 `module_id` int(11) NOT NULL,
 		 `access_level` varchar(20) NOT NULL,
 	 		PRIMARY KEY (`id`)
		)');
    }//up()

    public function down()
    {
    	$this->drop_table('cms_modules');
    	$this->drop_table('cms_roles');
    	$this->drop_table('cms_roles_access');
    }//down()
}
