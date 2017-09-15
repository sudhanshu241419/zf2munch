<?php

class AddColumnCommentsRestaurentUploadTemp extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE restaurant_upload_temp ADD COLUMN `comments` VARCHAR(255) AFTER  `assign_to`");
    }//up()

    public function down()
    {
    }//down()
}
