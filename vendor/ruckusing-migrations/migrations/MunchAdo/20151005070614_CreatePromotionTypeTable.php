<?php

class CreatePromotionTypeTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `promotionType` (
  `promotionTypeId` bigint(20) NOT NULL AUTO_INCREMENT,
  `promotionCatgeoryName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `promotionCategoryStatus` tinyint(4) NOT NULL DEFAULT '0',
   `updated_on` datetime NOT NULL,
  PRIMARY KEY (`promotionTypeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");
    }//up()

    public function down()
    {
    }//down()
}
