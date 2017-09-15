<?php

class ChangepreorderitemtableNotnull extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute('ALTER TABLE `pre_order_item` CHANGE `user_id` `user_id` INT( 11 ) NULL');
    }//up()

    public function down()
    {
    }//down()
}
