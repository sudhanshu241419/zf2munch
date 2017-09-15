<?php

class InsertPointsoucceRedeementry extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO `MunchAdo`.`point_source_detail` (`id`, `name`, `points`, `csskey`, `identifier`, `created_at`, `points_for`, `dindex`, `dstatus`) VALUES (NULL, 'Redeem', '15', 'i_redemption', 'i_redemption', '2015-10-21 12:34:56', 'ws', '29', '1')");
    }//up()

    public function down()
    {
    }//down()
}
