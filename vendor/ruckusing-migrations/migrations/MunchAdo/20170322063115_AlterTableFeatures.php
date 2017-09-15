<?php

class AlterTableFeatures extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `features` ADD  `pid` INT( 11 ) NOT NULL DEFAULT  '0' AFTER  `id`");
    }//up()

    public function down()
    {
    }//down()
}
