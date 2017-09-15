<?php

class AlterTableUsersWallpaper extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `users` ADD  `wallpaper` VARCHAR( 255 ) NULL DEFAULT NULL ");
    }//up()

    public function down()
    {
    }//down()
}
