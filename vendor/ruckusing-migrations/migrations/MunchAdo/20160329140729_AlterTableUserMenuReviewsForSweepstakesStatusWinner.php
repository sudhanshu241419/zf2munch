<?php

class AlterTableUserMenuReviewsForSweepstakesStatusWinner extends Ruckusing_Migration_Base
{
    public function up()
    {
        $query="ALTER TABLE  `user_menu_reviews` ADD  `sweepstakes_status_winner` ENUM( '0', '1', '2' ) NOT NULL DEFAULT  '0' COMMENT 'This field is used to sweeps takes to approve and disapprove image, 0 = disapprove, 1 = approved, 2 = Winner' AFTER  `image_status`";
        $this->execute($query);
    }//up()

    public function down()
    {
    }//down()
}
