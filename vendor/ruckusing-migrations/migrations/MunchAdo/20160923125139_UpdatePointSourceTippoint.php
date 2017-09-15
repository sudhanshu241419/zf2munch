<?php

class UpdatePointSourceTippoint extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE `MunchAdo`.`point_source_detail` SET `points` = '5' WHERE `point_source_detail`.`id` = 32");
    }//up()

    public function down()
    {
    }//down()
}
