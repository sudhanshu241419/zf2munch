<?php

class AlterUserorderCod extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->query("ALTER TABLE `user_orders`  ADD `cod` TINYINT NOT NULL DEFAULT '0' COMMENT 'cash on delevery' AFTER `cronsmsupdate`");
    }//up()

    public function down()
    {
    }//down()
}
