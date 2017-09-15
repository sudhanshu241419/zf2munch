<?php

class AlterTableMerchantRtration extends Ruckusing_Migration_Base
{
    public function up()
    {

       $this->execute("ALTER TABLE  `merchant_registration` CHANGE  `cardno`  `cardno` VARCHAR( 200 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
       $this->execute("ALTER TABLE `merchant_registration`  ADD `payment_mode` VARCHAR(20) NULL DEFAULT NULL AFTER `email`");
       $this->execute("ALTER TABLE  `merchant_registration` ADD  `cron_update` TINYINT( 1 ) NULL DEFAULT  '0'");
       $this->execute("ALTER TABLE  `merchant_registration` CHANGE  `package`  `package` VARCHAR( 10 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT  'A=>Advanced, P=>Premium, PP => Promotional Package'");
       $this->execute("ALTER TABLE  `merchant_registration` ADD  `campaign_start_date` DATETIME NULL");
       $this->execute("ALTER TABLE  `merchant_registration` ADD  `waiving_period` TINYINT NULL");
       $this->execute("ALTER TABLE  `merchant_registration` ADD  `expires_on` DATETIME NULL DEFAULT NULL");
     }//up()

    public function down()
    {
    }//down()
}
