<?php

class CreateTableEmailSubscription extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE `MunchAdo`.`email_subscription` (
`id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`email` VARCHAR( 255 ) NOT NULL ,
`source` VARCHAR( 255 ) NOT NULL ,
`created_on` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`status` ENUM( '0', '1' ) NOT NULL DEFAULT '0',
`comment` TEXT NOT NULL 
) ENGINE = InnoDB;");
    }//up()

    public function down()
    {
    }//down()
}
