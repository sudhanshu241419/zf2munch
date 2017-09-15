<?php

class UpdateTableUserOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_orders` ADD `cronUpdate` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `assignMuncher` ;");
    }//up()

    public function down()
    {
    }//down()
}
