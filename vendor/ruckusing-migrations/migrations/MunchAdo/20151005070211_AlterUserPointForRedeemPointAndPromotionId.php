<?php

class AlterUserPointForRedeemPointAndPromotionId extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `user_points` ADD  `redeemPoint` BIGINT NULL AFTER  `point_source` ,
ADD  `promotionId` BIGINT NULL AFTER  `redeemPoint`");
    }//up()

    public function down()
    {
    }//down()
}
