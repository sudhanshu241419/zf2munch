<?php

class InsertPointsourceReferralInvitation extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO `MunchAdo`.`point_source_detail` (`id`, `name`, `points`, `csskey`, `identifier`, `created_at`, `points_for`, `dindex`, `dstatus`) VALUES ('0', 'Dine & more referral inviter point when invitee join', '15', 'dinemorereferralinviter', 'dinemorereferralinviter', '2016-08-09 06:19:22', 'ap', '4', '1'), ('0', 'Dine & more referral invitee registration point', '250', 'dinemorereferral', 'dinemorereferralinvitee', '2016-08-09 06:19:22', 'ap', '4', '1')");
    }//up()

    public function down()
    {
    }//down()
}
