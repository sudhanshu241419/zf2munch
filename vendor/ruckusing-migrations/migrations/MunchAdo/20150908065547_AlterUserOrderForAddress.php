<?php

class AlterUserOrderForAddress extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_orders` ADD `address` VARCHAR( 255 ) NULL AFTER `apt_suite`");
    }//up()

    public function down()
    {
    }//down()
}
