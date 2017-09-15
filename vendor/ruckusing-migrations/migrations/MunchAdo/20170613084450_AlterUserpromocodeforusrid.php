<?php

class AlterUserpromocodeforusrid extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_promocodes` CHANGE `user_id` `user_id` INT(11) NULL DEFAULT '0'");
    }//up()

    public function down()
    {
    }//down()
}
