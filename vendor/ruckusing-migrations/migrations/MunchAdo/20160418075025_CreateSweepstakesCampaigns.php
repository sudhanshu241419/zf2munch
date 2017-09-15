<?php

class CreateSweepstakesCampaigns extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `sweepstakes_campaigns` (`id` int(11) NOT NULL AUTO_INCREMENT,`name` varchar(100) NOT NULL,`start_on` datetime NOT NULL,`end_date` datetime NOT NULL,`status` tinyint(1) NOT NULL DEFAULT '1',PRIMARY KEY (`id`)) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2");
    }//up()

    public function down()
    {
    }//down()
}
