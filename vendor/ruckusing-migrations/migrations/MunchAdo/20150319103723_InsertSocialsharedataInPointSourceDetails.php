<?php

class InsertSocialsharedataInPointSourceDetails extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO  `MunchAdo`.`point_source_detail` (`id` ,`name` ,`points` ,`csskey` ,`identifier` ,`created_at`) VALUES ('0',  'Facebook Share',  '10',  'i_facebookshare',  'facebookShare',  '2015-03-19 00:00:00')");
        $this->execute("INSERT INTO  `MunchAdo`.`point_source_detail` (`id` ,`name` ,`points` ,`csskey` ,`identifier` ,`created_at`) VALUES ('0',  'Twitter Share',  '10',  'i_twittershare',  'twitterShare',  '2015-03-19 00:00:00')");
    }//up()

    public function down()
    {
    }//down()
}
