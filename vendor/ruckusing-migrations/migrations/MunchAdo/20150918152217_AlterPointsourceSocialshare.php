<?php

class AlterPointsourceSocialshare extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE  `MunchAdo`.`point_source_detail` SET  `points` =  '3' WHERE  `point_source_detail`.`id` =18;");
        $this->execute("UPDATE  `MunchAdo`.`point_source_detail` SET  `points` =  '3' WHERE  `point_source_detail`.`id` =19;");
    }//up()

    public function down()
    {
    }//down()
}
