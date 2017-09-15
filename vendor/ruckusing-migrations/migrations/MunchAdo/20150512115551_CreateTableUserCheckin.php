<?php

class CreateTableUserCheckin extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE `MunchAdo`.`user_checkin` (
`id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`user_id` BIGINT NOT NULL ,
`restaurant_id` INT NOT NULL ,
`message` TEXT NOT NULL ,
`created_at` DATETIME NOT NULL 
) ENGINE = InnoDB;");
    }//up()

    public function down()
    {
    }//down()
}
