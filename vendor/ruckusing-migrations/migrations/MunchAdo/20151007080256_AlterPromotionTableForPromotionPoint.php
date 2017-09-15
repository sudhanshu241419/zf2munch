<?php

class AlterPromotionTableForPromotionPoint extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `promotions`  ADD `promotionPoints` BIGINT NOT NULL AFTER `promotionId`");
    }//up()

    public function down()
    {
    }//down()
}
