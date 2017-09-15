<?php

class AddColumnAccessTokenInUser extends Ruckusing_Migration_Base
{
    public function up()
    {
	$this->execute("ALTER TABLE `users` ADD `access_token` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}
