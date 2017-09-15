<?php

class Alterpromocodeforbudget extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `promocodes`  ADD `budget` INT NOT NULL DEFAULT '0' AFTER `restaurant_id`");
        $this->execute("ALTER TABLE `promocodes` CHANGE `promocodeType` `promocodeType` TINYINT(4) NOT NULL COMMENT '1=normal, 2=promotional,3=budget'");
    }//up()

    public function down()
    {
    }//down()
}
