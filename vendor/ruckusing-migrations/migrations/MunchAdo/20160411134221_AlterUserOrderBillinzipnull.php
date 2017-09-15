<?php

class AlterUserOrderBillinzipnull extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_orders` CHANGE `billing_zip` `billing_zip` VARCHAR(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}
