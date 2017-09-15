<?php

class InsertSweepstakesPoint extends Ruckusing_Migration_Base
{
    public function up()
    {
        $query = "INSERT INTO `MunchAdo`.`point_source_detail` (`id`, `name`, `points`, `csskey`, `identifier`, `created_at`, `points_for`, `dindex`, `dstatus`) VALUES ('0', 'Upload pictures from your Munch Ado experiences under sweepstakes restaurant', '50', 'i_sweepspostpictures', 'sweepstakes', '2016-03-31 16:12:17', 'ap', '14', '1')";
        $this->execute($query);
    }//up()

    public function down()
    {
    }//down()
}
