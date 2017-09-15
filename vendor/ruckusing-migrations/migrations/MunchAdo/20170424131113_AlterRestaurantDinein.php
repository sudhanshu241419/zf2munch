<?php

class AlterRestaurantDinein extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurant_dinein` CHANGE `alternate_date` `alternate_date` DATETIME NULL DEFAULT NULL");
        $this->execute("ALTER TABLE `restaurant_dinein` CHANGE `restaurant_offer` `restaurant_offer` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
        $this->execute("ALTER TABLE `restaurant_dinein` CHANGE `restaurant_instruction` `restaurant_instruction` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
        $this->execute("ALTER TABLE `restaurant_dinein` CHANGE `hold_time` `hold_time` INT NOT NULL DEFAULT '0'");
    }//up()

    public function down()
    {
    }//down()
}
