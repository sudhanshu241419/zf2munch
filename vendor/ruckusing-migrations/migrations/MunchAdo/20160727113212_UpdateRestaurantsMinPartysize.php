<?php

class UpdateRestaurantsMinPartysize extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute('UPDATE  `restaurants` SET min_partysize =2 WHERE 1');
    }//up()

    public function down()
    {
    }//down()
}
