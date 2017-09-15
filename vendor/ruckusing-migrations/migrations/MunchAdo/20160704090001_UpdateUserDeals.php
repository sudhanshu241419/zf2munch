<?php

class UpdateUserDeals extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `user_deals` ADD  `deal_status` TINYINT( 1 ) NOT NULL DEFAULT  '0' COMMENT  '0=> Active,1=>Inactive' AFTER  `deal_id` ;");
        $this->execute("ALTER TABLE  `user_deals` ADD  `availed` TINYINT( 1 ) NOT NULL DEFAULT  '0' COMMENT  '0=>No,1=>Yes' AFTER  `deal_status` ;");
    }//up()

    public function down()
    {
    }//down()
}
