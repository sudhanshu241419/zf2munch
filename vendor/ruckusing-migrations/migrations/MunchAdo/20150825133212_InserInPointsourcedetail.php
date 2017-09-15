<?php

class InserInPointsourcedetail extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO `point_source_detail` (
`id` ,
`name` ,
`points` ,
`csskey` ,
`identifier` ,
`created_at`
)
VALUES (
'0', 'Registrations with edu', '400', 'i_completeprofile', 'eduRegister', '2014-04-14 00:00:00'
)");
    }//up()

    public function down()
    {
    }//down()
}
