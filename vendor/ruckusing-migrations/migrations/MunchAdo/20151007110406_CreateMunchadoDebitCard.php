<?php

class CreateMunchadoDebitCard extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `munchado_debit_card` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL COMMENT 'ref=>users',
  `card_number` varchar(20) DEFAULT NULL,  
  `card_type` varchar(50) NOT NULL,
  `name_on_card` varchar(100) DEFAULT NULL,
  `expired_on` varchar(10) DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL COMMENT '1=>active, 2=> deactive',  
  PRIMARY KEY (`id`),
  KEY `FK_user_cards` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
    }//up()

    public function down()
    {
    }//down()
}
