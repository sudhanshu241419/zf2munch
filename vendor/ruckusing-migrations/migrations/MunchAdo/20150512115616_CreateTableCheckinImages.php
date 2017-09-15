<?php

class CreateTableCheckinImages extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute(" CREATE TABLE `MunchAdo`.`checkin_images` (
`id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`checkin_id` BIGINT NOT NULL ,
`image_name` VARCHAR( 255 ) NOT NULL ,
`image_path` VARCHAR( 255 ) NOT NULL ,
`status` ENUM( '0', '1' ) NOT NULL DEFAULT '0'
) ENGINE = InnoDB");
    }//up()

    public function down()
    {
    }//down()
}
