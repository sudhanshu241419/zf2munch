<?php

class AddColumnNameInUserStatistics extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_statistics` ADD `reservation_with_count` INT NOT NULL DEFAULT '0' AFTER `groups_order_count`");
    }//up()

    public function down()
    {
    }//down()
}
