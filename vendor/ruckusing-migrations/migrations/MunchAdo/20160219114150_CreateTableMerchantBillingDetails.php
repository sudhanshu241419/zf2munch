<?php

class CreateTableMerchantBillingDetails extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `merchant_billing_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL,
  `billing_amount` int(11) DEFAULT NULL,
  `total_payment_cycles` tinyint(4) DEFAULT NULL,
  `charged_cycles` tinyint(1) DEFAULT NULL,
  `billing_date` datetime DEFAULT NULL,
  `next_billing_date` datetime DEFAULT NULL,
  `check_number` varchar(50) DEFAULT NULL,
  `check_date` datetime DEFAULT NULL,
  `check_issuer` varchar(50) DEFAULT NULL,
  `expires_on` datetime NOT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
    }//up()

    public function down()
    {
    }//down()
}
