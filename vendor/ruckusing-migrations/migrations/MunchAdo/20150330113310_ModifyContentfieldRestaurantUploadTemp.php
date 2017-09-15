<?php

class ModifyContentfieldRestaurantUploadTemp extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurant_upload_temp` CHANGE `contents` `contents` LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");
    }//up()

    public function down()
    {
    }//down()
}
