<?php

class AlterRestaurantDineinArchiveField extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurant_dinein`  ADD `archive` TINYINT NOT NULL DEFAULT '0' AFTER `status`");
    }//up()

    public function down()
    {
    }//down()
}
