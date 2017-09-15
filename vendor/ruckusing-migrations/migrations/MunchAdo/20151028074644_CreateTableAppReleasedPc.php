<?php

class CreateTableAppReleasedPc extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `app_released_campaign` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `phone` varchar(20) DEFAULT NULL,
        `email` varchar(50) DEFAULT NULL,
        PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
    }//up()

    public function down()
    {
    }//down()
}
