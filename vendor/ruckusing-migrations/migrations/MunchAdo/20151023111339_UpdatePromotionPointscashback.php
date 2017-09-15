<?php

class UpdatePromotionPointscashback extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE `promotions` SET `promotionPoints` = '600' WHERE `promotionId` =2");
    }//up()

    public function down()
    {
    }//down()
}
