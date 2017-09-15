<?php

class AlterUserOrderForRedeemPoint extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_orders` CHANGE `redeem_point` `redeem_point` INT(11) NOT NULL DEFAULT '0'");
    }//up()

    public function down()
    {
    }//down()
}
