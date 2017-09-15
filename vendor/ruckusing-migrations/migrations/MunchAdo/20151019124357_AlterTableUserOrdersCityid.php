<?php

class AlterTableUserOrdersCityid extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `user_orders` ADD  `city_id` INT NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}
