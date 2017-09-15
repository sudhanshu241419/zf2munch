<?php

class AlterTableRestaurantInstagram extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurants` ADD  `instagram_url` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `pinterest_url`");
    }//up()

    public function down()
    {
    }//down()
}
