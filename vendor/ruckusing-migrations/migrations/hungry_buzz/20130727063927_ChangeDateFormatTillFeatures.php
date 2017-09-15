<?php

class ChangeDateFormatTillFeatures extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `archives` CHANGE `created_on` `created_on` DATETIME NULL");
    	$this->execute("ALTER TABLE `chefs` CHANGE `created_on` `created_on` DATETIME NULL");
    	$this->execute("ALTER TABLE `cms_users` CHANGE `create_at` `create_at` DATETIME NULL");
    	$this->execute("ALTER TABLE `cms_users` CHANGE `lastvisit_at` `lastvisit_at` DATETIME NULL");
    	$this->execute("ALTER TABLE `cuisines` CHANGE `created_on` `created_on` DATETIME NULL");

    }//up()

    public function down()
    {
    	$this->remove_column("archives","created_on");
    	$this->remove_column("chefs","created_on");
    	$this->remove_column("cms_users","create_at");
    	$this->remove_column("cms_users","lastvisit_at");
    	$this->remove_column("cuisines","created_on");
    	
    }//down()
}
