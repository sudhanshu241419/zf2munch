<?php

class AddIsBrowseOnly extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute('ALTER TABLE  `cities` ADD  `is_browse_only` TINYINT NOT NULL DEFAULT  1');
    	$this->execute("UPDATE  `hungry_buzz`.`cities` SET  `is_browse_only` =  0 WHERE  `cities`.`city_name` = 'San Francisco';");
    	$this->execute("UPDATE  `hungry_buzz`.`cities` SET  `is_browse_only` =  0 WHERE  `cities`.`city_name` = 'Austin' AND `cities`.`state_code` = 'TX';");
    }//up()

    public function down()
    {
    }//down()
}
