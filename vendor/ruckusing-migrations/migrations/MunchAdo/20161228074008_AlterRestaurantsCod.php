<?php

class AlterRestaurantsCod extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->query("ALTER TABLE `restaurants`  ADD `cod` TINYINT NOT NULL DEFAULT '0' COMMENT 'Cash on delivery' AFTER `pre_paid_enable`");
    }//up()

    public function down()
    {
    }//down()
}
