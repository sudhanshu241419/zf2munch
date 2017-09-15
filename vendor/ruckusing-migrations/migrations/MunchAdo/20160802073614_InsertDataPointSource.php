<?php

class InsertDataPointSource extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO `MunchAdo`.`point_source_detail` (`id`, `name`, `points`, `csskey`, `identifier`, `created_at`, `points_for`, `dindex`, `dstatus`) VALUES ('0', 'Dine & more awards registration', '100', 'i_awards_registration', 'awardsregistration', '2016-08-02 03:15:52', 'bt', '1', '1')");
        $this->execute("INSERT INTO `MunchAdo`.`point_source_detail` (`id`, `name`, `points`, `csskey`, `identifier`, `created_at`, `points_for`, `dindex`, `dstatus`) VALUES ('0', 'Dine & more awards first order', '50', 'i_awards_firstorder', 'awardsfirstorder', '2016-08-02 03:15:52', 'bt', '1', '1')");
        $this->execute("INSERT INTO `MunchAdo`.`point_source_detail` (`id`, `name`, `points`, `csskey`, `identifier`, `created_at`, `points_for`, `dindex`, `dstatus`) VALUES ('0', 'Dine & more awards referring friend', '250', 'i_awards_referingfriend', 'awardsreferingfriend', '2016-08-02 03:15:52', 'bt', '1', '1')");
        $this->execute("INSERT INTO `MunchAdo`.`point_source_detail` (`id`, `name`, `points`, `csskey`, `identifier`, `created_at`, `points_for`, `dindex`, `dstatus`) VALUES ('0', 'Dine & more awards ordering 50', '100', 'i_awards_ordering50', 'awardsordering50', '2016-08-02 03:15:52', 'bt', '1', '1')");
        $this->execute("INSERT INTO `MunchAdo`.`point_source_detail` (`id`, `name`, `points`, `csskey`, `identifier`, `created_at`, `points_for`, `dindex`, `dstatus`) VALUES ('0', 'Dine & more awards reservation', '100', 'i_awards_reservation', 'awardsreservation', '2016-08-02 03:15:52', 'bt', '1', '1')");
        $this->execute("INSERT INTO `MunchAdo`.`point_source_detail` (`id`, `name`, `points`, `csskey`, `identifier`, `created_at`, `points_for`, `dindex`, `dstatus`) VALUES ('0', 'Dine & more awards review', '100', 'i_awards_review', 'awardsreview', '2016-08-02 03:15:52', 'bt', '1', '1')");
    }//up()

    public function down()
    {
    }//down()
}
