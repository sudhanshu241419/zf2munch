<?php

class AddColumnRestaurantReview extends Ruckusing_Migration_Base
{
    public function up()
    {
      $this->execute("ALTER TABLE `restaurant_reviews` ADD `is_read` TINYINT NOT NULL DEFAULT '0' COMMENT '0=>unread,1=>readed' AFTER `source_url`");
    }//up()

    public function down()
    {
    }//down()
}
