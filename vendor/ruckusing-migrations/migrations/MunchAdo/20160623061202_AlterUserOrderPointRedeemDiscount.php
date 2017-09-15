<?php

class AlterUserOrderPointRedeemDiscount extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `user_orders` ADD  `redeemed_point` FLOAT NULL DEFAULT  '0' COMMENT  'discount amount against point redeem' AFTER `deal_discount` ;");
    }//up()

    public function down()
    {
    }//down()
}
