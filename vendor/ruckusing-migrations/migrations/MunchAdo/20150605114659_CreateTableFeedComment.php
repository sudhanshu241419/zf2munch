<?php

class CreateTableFeedComment extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE `MunchAdo`.`feed_comment` (
`id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`feed_id` INT NOT NULL ,
`user_id` BIGINT NOT NULL ,
`comment` TEXT NOT NULL ,
`status` ENUM( '0', '1' ) NOT NULL DEFAULT '1',
`created_on` DATETIME NOT NULL 
) ENGINE = InnoDB;");
    }//up()

    public function down()
    {
    }//down()
}
