<?php

class AlterCheckinImageTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `checkin_images` CHANGE  `status`  `status` ENUM(  '0',  '1',  '4',  '5' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0'");
    }//up()

    public function down()
    {
    }//down()
}
