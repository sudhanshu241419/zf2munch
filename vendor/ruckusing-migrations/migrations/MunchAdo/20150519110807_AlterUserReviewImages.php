<?php

class AlterUserReviewImages extends Ruckusing_Migration_Base
{
    public function up()
    {
         $this->execute("ALTER TABLE `user_review_images` DROP `order_item_id` ,DROP `order_id` ;");
         $this->execute("ALTER TABLE `user_review_images` CHANGE `image_path` `image_url` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;");
         $this->execute("ALTER TABLE `user_review_images` ADD `image` VARCHAR( 255 ) NOT NULL AFTER `user_review_id` ;");
    }//up()

    public function down()
    {
    }//down()
}
