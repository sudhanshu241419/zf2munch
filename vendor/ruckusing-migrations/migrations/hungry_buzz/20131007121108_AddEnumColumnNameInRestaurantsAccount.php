<?php

class AddEnumColumnNameInRestaurantsAccount extends Ruckusing_Migration_Base
{
    public function up()
    {
    $this->execute("ALTER TABLE  `restaurant_accounts` CHANGE  `role`  `role` ENUM(  'a',  'm',  'o' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL");		
    }//up()

    public function down()
    {
    }//down()
}
