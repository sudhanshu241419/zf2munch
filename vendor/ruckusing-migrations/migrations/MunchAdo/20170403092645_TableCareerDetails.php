<?php

class TableCareerDetails extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `career_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform` varchar(50) NOT NULL,
  `location` varchar(50) NOT NULL,
  `department` varchar(50) NOT NULL,
  `position` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `skills` text NOT NULL,
  `responsibilty` text NOT NULL,
  `should_have` text NOT NULL,
  `additional_information_heading` varchar(256) NOT NULL,
  `additional_information` text NOT NULL,
  `jd_name` varchar(256) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1=>open, 0=>close',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
    }//up()

    public function down()
    {
    }//down()
}
