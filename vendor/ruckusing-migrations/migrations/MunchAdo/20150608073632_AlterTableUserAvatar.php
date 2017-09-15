<?php

class AlterTableUserAvatar extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_avatar` CHANGE `avatar_type` `avatar_id` INT NOT NULL ,
CHANGE `is_earned` `total_earned` INT NOT NULL DEFAULT '0';");
    }//up()

    public function down()
    {
    }//down()
}
