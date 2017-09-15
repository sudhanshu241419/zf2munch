<?php

class AlterPromocode extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `promocodes`  ADD `promo_code` VARCHAR(255) NULL DEFAULT NULL AFTER `end_date`");
    }//up()

    public function down()
    {
    }//down()
}
