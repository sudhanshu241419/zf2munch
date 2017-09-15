<?php

class UpdateTablePointSource extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO `point_source_detail` (
`id` ,
`name` ,
`points` ,
`csskey` ,
`identifier` ,
`created_at` ,
`points_for` ,
`dindex` ,
`dstatus`
)
VALUES (
'0', 'Check in with friend and photo', '7', 'checkFriendPhoto', 'checkFriendPhoto', '2013-06-14 06:19:22', 'ap', '15', '1'
);");
    }//up()

    public function down()
    {
    }//down()
}
