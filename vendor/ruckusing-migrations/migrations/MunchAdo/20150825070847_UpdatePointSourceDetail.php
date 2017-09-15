<?php

class UpdatePointSourceDetail extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE `MunchAdo`.`point_source_detail` SET `points` = '100' WHERE `point_source_detail`.`identifier` ='normalRegister'");
        $this->execute("UPDATE `MunchAdo`.`point_source_detail` SET `points` = '100' WHERE `point_source_detail`.`identifier` ='socialRegister'");
    }//up()

    public function down()
    {
    }//down()
}
