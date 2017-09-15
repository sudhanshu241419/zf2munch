<?php

class AlterUserordersSpectialchecks extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `user_orders` CHANGE  `special_checks`  `special_checks` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}
