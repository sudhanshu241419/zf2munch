<?php

class AlterTablePointSourceDetailUpdateRows extends Ruckusing_Migration_Base
{
    public function up()
    {
         $this->execute("UPDATE `MunchAdo`.`point_source_detail` SET `identifier` = 'completeProfile' WHERE `point_source_detail`.`id` =9;");
        $this->execute("UPDATE `MunchAdo`.`point_source_detail` SET `identifier` = 'groupOrderPlaced' WHERE `point_source_detail`.`id` =2;");
        $this->execute("UPDATE `MunchAdo`.`point_source_detail` SET `identifier` = 'postOnTwitter' WHERE `point_source_detail`.`id` =11;");
        $this->execute("UPDATE `MunchAdo`.`point_source_detail` SET `identifier` = 'postOnFacebook' WHERE `point_source_detail`.`id` =10;");
                $this->execute("UPDATE `MunchAdo`.`point_source_detail` SET `identifier` = 'reportError' WHERE `point_source_detail`.`id` =8;");
        $this->execute("UPDATE `MunchAdo`.`point_source_detail` SET `identifier` = 'purchaseDealCoupon' WHERE `point_source_detail`.`id` =4;");
        $this->execute("ALTER TABLE `point_source_detail` CHANGE `identifier` `identifier` VARCHAR( 30 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;");
    }//up()

    public function down()
    {
    }//down()
}
