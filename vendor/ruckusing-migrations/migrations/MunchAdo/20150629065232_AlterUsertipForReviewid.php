<?php

class AlterUsertipForReviewid extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute('ALTER TABLE `user_tips` ADD `review_id` INT NOT NULL AFTER `restaurant_id` ');
    }//up()

    public function down()
    {
    }//down()
}
