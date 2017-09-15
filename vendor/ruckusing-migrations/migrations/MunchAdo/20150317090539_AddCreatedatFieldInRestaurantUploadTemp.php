<?php

class AddCreatedatFieldInRestaurantUploadTemp extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurant_upload_temp` ADD `created_at` TIMESTAMP NOT NULL AFTER `status`");
    }//up()

    public function down()
    {
    }//down()
}
