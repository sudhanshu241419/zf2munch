<?php

class UpdatePromocodes extends Ruckusing_Migration_Base
{
    public function up()
    {
     $this->execute("ALTER TABLE  `promocodes` ADD  `cronUpdate` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `description` ;");
     $this->execute("UPDATE `promocodes` set `cronUpdate`=1");    
    }//up()

    public function down()
    {
    }//down()
}
