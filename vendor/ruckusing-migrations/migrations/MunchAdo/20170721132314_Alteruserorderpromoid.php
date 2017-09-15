<?php

class Alteruserorderpromoid extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_orders`  ADD `promocodeid` INT NOT NULL DEFAULT '0' AFTER `promocode_discount`");
    }//up()

    public function down()
    {
    }//down()
}
