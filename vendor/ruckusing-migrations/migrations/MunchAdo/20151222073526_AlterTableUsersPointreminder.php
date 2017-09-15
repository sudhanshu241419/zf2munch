<?php

class AlterTableUsersPointreminder extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `users` ADD  `pointsreminder` ENUM(  '0',  '1' ) NOT NULL DEFAULT  '0'");
    }//up()

    public function down()
    {
    }//down()
}
