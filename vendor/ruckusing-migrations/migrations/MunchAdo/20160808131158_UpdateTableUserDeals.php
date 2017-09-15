<?php

class UpdateTableUserDeals extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `user_deals` ADD  `read` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `date` ;");
    }//up()

    public function down()
    {
    }//down()
}
