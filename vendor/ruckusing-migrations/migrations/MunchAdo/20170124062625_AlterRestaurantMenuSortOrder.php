<?php

class AlterRestaurantMenuSortOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurants` ADD  `menu_sort_order` INT( 1 ) NOT NULL AFTER  `pre_paid_enable`");
    }//up()

    public function down()
    {
    }//down()
}
