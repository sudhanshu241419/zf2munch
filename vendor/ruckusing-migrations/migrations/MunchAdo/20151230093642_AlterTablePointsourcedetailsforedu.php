<?php

class AlterTablePointsourcedetailsforedu extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO  `MunchAdo`.`point_source_detail` (`id` ,`name` ,`points` ,`csskey` ,`identifier` ,`created_at` ,`points_for` ,`dindex` ,`dstatus`)VALUES 
            (49 ,  'Register With .edu Account',  '400',  'i_completeprofile',  'eduRegister',  '2015-12-30 07:33:17',  'ws',  '30',  '1')");
    }//up()

    public function down()
    {
    }//down()
}
