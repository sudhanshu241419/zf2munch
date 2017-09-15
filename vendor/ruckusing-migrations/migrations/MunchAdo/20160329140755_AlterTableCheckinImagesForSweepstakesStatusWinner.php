<?php

class AlterTableCheckinImagesForSweepstakesStatusWinner extends Ruckusing_Migration_Base
{
    public function up()
    {
        $query="ALTER TABLE  `checkin_images` ADD  `sweepstakes_status_winner` ENUM( '0', '1', '2' ) NOT NULL DEFAULT  '0' COMMENT 'This field is used to sweeps takes to approve and disapprove image, 0 = disapprove, 1 = approved, 2 = Winner' AFTER  `status`";
        $this->execute($query);
    }//up()

    public function down()
    {
    }//down()
}
