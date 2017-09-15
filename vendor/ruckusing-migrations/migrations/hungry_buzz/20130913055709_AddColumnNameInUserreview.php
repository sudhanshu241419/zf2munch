<?php

class AddColumnNameInUserreview extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_reviews` CHANGE `status` `status` TINYINT( 1 ) NULL DEFAULT '0' COMMENT '0=>new, 1=> approved, 2=>disapproved, 3=>deleted'");
        $this->execute("ALTER TABLE `user_reviews` ADD `approved_by` INT NOT NULL AFTER `status`");
        $this->execute("ALTER TABLE `user_reservations` CHANGE `status` `status` TINYINT( 1 ) NULL DEFAULT NULL COMMENT '0=>archived, 1=>upcoming, 2=>canceled, 3=>rejected, 4=>confirmed'");
    }//up()

    public function down()
    {
    	$this->remove_column("user_reviews","approved_by");
    }//down()
}
