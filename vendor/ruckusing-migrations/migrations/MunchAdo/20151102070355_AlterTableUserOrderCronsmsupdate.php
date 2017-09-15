<?php

class AlterTableUserOrderCronsmsupdate extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `user_orders` ADD  `cronsmsupdate` TINYINT( 1 ) NOT NULL DEFAULT  '0' COMMENT  'Send SMS and update Cron status here.'");
    }//up()

    public function down()
    {
    }//down()
}
