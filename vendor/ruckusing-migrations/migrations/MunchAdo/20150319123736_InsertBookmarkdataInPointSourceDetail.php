<?php

class InsertBookmarkdataInPointSourceDetail extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE  `MunchAdo`.`point_source_detail` SET  `identifier` =  'loveRestaurant' WHERE  `point_source_detail`.`id` =16");
        $this->execute("INSERT INTO  `MunchAdo`.`point_source_detail` (`id` ,`name` ,`points` ,`csskey` ,`identifier` ,`created_at`) VALUES (
'0',  'Been There Restaurant',  '1',  'i_beenthererestaurant',  'beenthererestaurant',  '2015-03-19 00:00:00'
)");
        $this->execute("INSERT INTO  `MunchAdo`.`point_source_detail` (`id` ,`name` ,`points` ,`csskey` ,`identifier` ,`created_at`) VALUES (
'0',  'Crave It Restaurant',  '1',  'i_craveitrestaurant',  'craveitrestaurant',  '2015-03-19 00:00:00'
)");
    }//up()

    public function down()
    {
    }//down()
}
