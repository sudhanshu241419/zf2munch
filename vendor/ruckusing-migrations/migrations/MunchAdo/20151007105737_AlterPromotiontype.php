<?php

class AlterPromotiontype extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `promotionType` CHANGE `promotionCatgeoryName` `promotionTypeName` VARCHAR( 255 ) NOT NULL");
        $this->execute("ALTER TABLE `promotionType` CHANGE `promotionCategoryStatus` `promotionTypeStatus` TINYINT( 4 ) NOT NULL DEFAULT '0'");
    }//up()

    public function down()
    {
    }//down()
}
