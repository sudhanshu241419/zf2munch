<?php

class CreateUserTips extends Ruckusing_Migration_Base
{
    public function up()
    {
         $this->execute("CREATE TABLE `MunchAdo`.`user_tips` (
            `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `user_id` BIGINT NOT NULL ,
            `restaurant_id` INT NOT NULL ,
            `tip` TEXT NOT NULL ,
            `status` ENUM( '0', '1' ) NOT NULL DEFAULT '0',
            `created_at` DATETIME NOT NULL 
            ) ENGINE = InnoDB;");
    }//up()

    public function down()
    {
    }//down()
}
