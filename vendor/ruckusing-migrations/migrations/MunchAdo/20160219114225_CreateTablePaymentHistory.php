<?php

class CreateTablePaymentHistory extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `payment_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL,
  `amount` float NOT NULL,
  `status` tinyint(4) NOT NULL,
  `remark` text NOT NULL,
  `payment_details` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
    }//up()

    public function down()
    {
    }//down()
}
