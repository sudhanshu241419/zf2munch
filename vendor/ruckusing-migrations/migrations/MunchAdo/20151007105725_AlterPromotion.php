<?php

class AlterPromotion extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `promotions` CHANGE `promotionSponsorByID` `promotionSponsorByID` BIGINT( 20 ) NOT NULL DEFAULT '0' COMMENT '0=munchado other than restaurant id'");
        $this->execute("ALTER TABLE `promotions` CHANGE `PromotionCategoryAmount` `promotionCategoryAmount` DECIMAL( 10, 2 ) NULL DEFAULT NULL");
        $this->execute("ALTER TABLE `promotions` CHANGE `promotionType` `promotionTypeId` BIGINT NOT NULL");
        $this->execute("ALTER TABLE `promotions` CHANGE `promotionCategoryId` `promotionCategoryId` BIGINT NOT NULL");
    }//up()

    public function down()
    {
    }//down()
}
