<?php

class AlterTableUserTip extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute('ALTER TABLE `user_tips` ADD `approved_date` DATETIME NULL AFTER `created_at`;');
    }//up()

    public function down()
    {
    }//down()
}
