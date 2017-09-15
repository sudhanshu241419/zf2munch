<?php

class InsertPointSourceDetail extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO `point_source_detail`(`id`,`name`,`points`,`csskey`,`identifier`,`created_at`
)
VALUES(
'0','Check in','2','checkIn','checkIn','2013-06-14 06:19:22'
),
(
'0','Check menu ','3','checkMenu','checkMenu','2013-06-14 06:19:22'
),(
'0', 'Check photo ', '5', 'checkPhoto', 'checkPhoto', '2013-06-14 06:19:22'
),(
'0', 'Check friend ', '3', 'checkFriend', 'checkFriend', '2013-06-14 06:19:22'
),(
'0', 'Check menu photo ', '7', 'checkMenuPhoto', 'checkMenuPhoto', '2013-06-14 06:19:22'
),(
'0', 'Check menu friend ', '7', 'checkMenuFriend', 'checkMenuFriend', '2013-06-14 06:19:22'
), (
'0', 'Check menu photo friend ', '7', 'checkMenuPhotoFriend', 'checkMenuPhotoFriend', '2013-06-14 06:19:22'
)");
    }//up()

    public function down()
    {
    }//down()
}
