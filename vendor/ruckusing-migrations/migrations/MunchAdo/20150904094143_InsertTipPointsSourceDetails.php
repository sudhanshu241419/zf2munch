<?php

class InsertTipPointsSourceDetails extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO `point_source_detail`(`id`,`name`,`points`,`csskey`,`identifier`,`created_at`
)
VALUES(
'0','Leave A Tip','3','leaveTip','leaveTip','2015-09-03 06:19:22'
)");
    }//up()

    public function down()
    {
    }//down()
}
