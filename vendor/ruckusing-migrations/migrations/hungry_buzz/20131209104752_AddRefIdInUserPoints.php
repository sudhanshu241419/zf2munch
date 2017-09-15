<?php

class AddRefIdInUserPoints extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_points` ADD `ref_id` INT NULL ");
    }//up()

    public function down()
    {
    }//down()
}
