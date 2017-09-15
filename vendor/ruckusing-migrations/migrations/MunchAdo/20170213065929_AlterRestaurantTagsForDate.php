<?php

class AlterRestaurantTagsForDate extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurant_tags` ADD  `created_at` DATETIME NOT NULL AFTER  `rest_short_url` ,
ADD  `updated_at` DATETIME NOT NULL AFTER  `created_at` ");
    }//up()

    public function down()
    {
    }//down()
}
