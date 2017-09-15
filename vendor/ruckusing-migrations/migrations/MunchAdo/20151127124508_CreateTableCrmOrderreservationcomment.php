<?php

class CreateTableCrmOrderreservationcomment extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `crm_order_reservation_comments` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `order_id` int(11) DEFAULT NULL,
          `reservation_id` int(11) DEFAULT NULL,
          `ctc` varchar(500) DEFAULT NULL,
          `process_adherence` varchar(100) DEFAULT NULL,
          `voice_and_accent` varchar(100) DEFAULT NULL,
          `script_adherence` varchar(100) DEFAULT NULL,
          `refund` varchar(100) DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `order_id` (`order_id`),
          UNIQUE KEY `reservation_id` (`reservation_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 
        ");
    }//up()

    public function down()
    {
    }//down()
}
