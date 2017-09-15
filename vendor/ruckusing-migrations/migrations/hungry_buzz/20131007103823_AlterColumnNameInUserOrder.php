<?php

class AlterColumnNameInUserOrder extends Ruckusing_Migration_Base
{
    public function up()
    {
     $this->execute("ALTER TABLE  `user_orders` CHANGE  `appproved_by`  `approved_by` INT( 11 ) NOT NULL");
    }//up()

    public function down()
    {
    }//down()
}
