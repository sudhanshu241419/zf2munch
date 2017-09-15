<?php

class AlterRestaurantForMaxPartysize extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurants` CHANGE  `max_partysize`  `min_partysize` INT( 5 ) NULL DEFAULT NULL");
    }//up()

    public function down()
    {
    }//down()
}
