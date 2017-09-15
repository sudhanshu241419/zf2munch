<?php

class CreateTableUserEatingHabits extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `user_eating_habits` (
		`id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        `user_id` BIGINT NOT NULL ,
        `favorite_beverage` VARCHAR( 255 ) NOT NULL ,
        `where_do_you_go` VARCHAR( 255 ) NOT NULL ,
        `comfort_food` VARCHAR( 255 ) NOT NULL ,
        `favorite_food` VARCHAR( 255 ) NOT NULL ,
        `dinner_with` VARCHAR( 255 ) NOT NULL ,
        `created_on` DATETIME NOT NULL ,
        `updated_on` DATETIME NOT NULL 
		) ENGINE=InnoDB;");
    }//up()

    public function down()
    {
    }//down()
}
