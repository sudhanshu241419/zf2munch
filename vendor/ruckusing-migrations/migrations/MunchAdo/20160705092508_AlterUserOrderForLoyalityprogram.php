<?php

class AlterUserOrderForLoyalityprogram extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_orders` CHANGE `redeemed_point` `redeemed_point` DECIMAL( 10, 2 ) NULL DEFAULT '0.00' COMMENT'discount amount against point redeem'");
        $this->execute("ALTER TABLE `user_orders` CHANGE `redeemed_point` `pay_via_point` DECIMAL(10,2) NULL DEFAULT '0.00' COMMENT 'discount amount against point redeem'");
        $this->execute("ALTER TABLE `user_orders`  ADD `pay_via_card` DECIMAL(10,2) NOT NULL DEFAULT '0.00' AFTER `order_amount`");
        $this->execute("ALTER TABLE `user_orders`  ADD `redeem_point` INT NOT NULL AFTER `pay_via_point`");
    }//up()

    public function down()
    {
    }//down()
}
