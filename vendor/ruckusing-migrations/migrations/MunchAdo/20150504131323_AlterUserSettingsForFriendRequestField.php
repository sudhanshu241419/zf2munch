<?php

class AlterUserSettingsForFriendRequestField extends Ruckusing_Migration_Base
{
    public function up()
    {
         $this->execute("ALTER TABLE `user_settings` ADD `friend_request` TINYINT NULL AFTER `comments_on_reviews` ;");
    }//up()

    public function down()
    {
    }//down()
}
