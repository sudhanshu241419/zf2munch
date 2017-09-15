<?php

class AlterSweepstakesCampaignsAddRestid extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `sweepstakes_campaigns` ADD  `rest_id` VARCHAR( 200 ) NOT NULL");
    }//up()

    public function down()
    {
    }//down()
}
