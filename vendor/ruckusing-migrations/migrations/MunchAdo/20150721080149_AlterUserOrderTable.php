<?php

class AlterUserOrderTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_orders` CHANGE `card_number` `card_number` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
        $this->execute("ALTER TABLE `user_orders` ADD `order_pass_through` TINYINT NOT NULL DEFAULT '0' COMMENT 'If order_pass_through is 1 then cc detail will saved in cc detail temp table and payment will not done' AFTER `actual_amount`");
    }//up()

    public function down()
    {
    }//down()
}
