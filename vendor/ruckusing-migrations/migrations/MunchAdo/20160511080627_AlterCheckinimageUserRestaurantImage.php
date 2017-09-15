<?php

class AlterCheckinimageUserRestaurantImage extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `checkin_images` CHANGE `sweepstakes_status_winner` `sweepstakes_status_winner` ENUM('0','1','2','3') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0' COMMENT 'This field is used to sweeps takes to approve and disapprove image, 0 = disapprove, 1 = approved, 2 = Winner, 3 = Duplicate'");
        $this->execute("ALTER TABLE `user_restaurant_image` CHANGE `sweepstakes_status_winner` `sweepstakes_status_winner` ENUM('0','1','2','3') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0' COMMENT 'This field is used to sweeps takes to approve and disapprove image, 0 = disapprove, 1 = approved, 2 = Winner, 3 = duplicate entry'");
    }//up()

    public function down()
    {
    }//down()
}
