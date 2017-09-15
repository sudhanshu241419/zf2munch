<?php

class AlterRestaurantForMaxPartysizeDefault extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurants` CHANGE  `min_partysize`  `min_partysize` INT( 5 ) NOT NULL DEFAULT  '0'");
    }//up()

    public function down()
    {
    }//down()
}
