<?php

class AddColumnInRestaurantTags extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurant_tags` ADD  `city_id` INT( 11 ) NOT NULL AFTER  `restaurant_id`");
    }//up()

    public function down()
    {
    }//down()
}
