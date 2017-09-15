<?php

class UpdatePointSourceForIdentifier extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("UPDATE `point_source_detail` SET `identifier` = 'acceptReservation' WHERE `csskey` ='i_reserve_a_table active'");
    	
    }//up()

    public function down()
    {
    }//down()
}
