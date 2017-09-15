<?php

class AlterSweepstakesCampaigns extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `sweepstakes_campaigns` CHANGE `rest_id` `rest_id` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");
    }//up()

    public function down()
    {
    }//down()
}
