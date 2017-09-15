<?php

class AddServealcohalFieldRestaurant extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurants` ADD `serve_alcohal` TINYINT NOT NULL DEFAULT '0' AFTER `allowed_zip`");
    }//up()

    public function down()
    {
    }//down()
}
