<?php

class AlterRestaurantPrePaidEnable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurants` ADD  `pre_paid_enable` TINYINT( 1 ) NOT NULL DEFAULT  '0' COMMENT  '0=>No,1=>Yes' AFTER  `featured`");
    }//up()

    public function down()
    {
    }//down()
}
