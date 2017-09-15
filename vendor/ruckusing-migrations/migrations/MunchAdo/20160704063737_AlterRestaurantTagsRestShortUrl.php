<?php

class AlterRestaurantTagsRestShortUrl extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurant_tags` ADD  `rest_short_url` VARCHAR( 255 ) NULL COMMENT  'for dine&more' AFTER  `tag_id`");
    }//up()

    public function down()
    {
    }//down()
}
