<?php

class CreatePromotionsTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `promotions` (
  `promotionId` bigint(20) NOT NULL AUTO_INCREMENT, 
  `promotionSponsorByID` bigint(20) NOT NULL, 
  `promotionCategoryId` tinyint(5) NOT NULL, 
  `minimumOrder` decimal(10,2) DEFAULT NULL,
  `promotionName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `promotionDesc` text COLLATE utf8_unicode_ci NOT NULL,
  `promotionType` tinyint(5) NOT NULL,
  `promotionImage` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `promotionDisplayOrder` tinyint(5) NOT NULL,
  `promotionStartDate` datetime NOT NULL,
  `promotionEndDate` datetime NOT NULL,
  `promotionStatus` tinyint(5) NOT NULL,
  `PromotionCategoryAmount` decimal(10,2) DEFAULT NULL,
  `promotionCreatedOn` datetime NOT NULL,
  `promotionUpdatedOn` datetime NOT NULL,
  PRIMARY KEY (`promotionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");
    }//up()

    public function down()
    {
    }//down()
}
