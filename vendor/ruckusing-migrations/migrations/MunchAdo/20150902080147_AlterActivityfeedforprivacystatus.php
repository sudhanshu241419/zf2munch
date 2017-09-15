<?php

class AlterActivityfeedforprivacystatus extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `activity_feed` ADD `privacy_status` TINYINT NOT NULL DEFAULT '0' AFTER `status`");
    }//up()

    public function down()
    {
    }//down()
}
