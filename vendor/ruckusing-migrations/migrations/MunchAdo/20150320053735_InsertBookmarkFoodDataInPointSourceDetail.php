<?php

class InsertBookmarkFoodDataInPointSourceDetail extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE `MunchAdo`.`point_source_detail` SET `identifier` = 'craveFood' WHERE `point_source_detail`.`id` =15");
        $this->execute("UPDATE `MunchAdo`.`point_source_detail` SET `identifier` = 'loveFood' WHERE `point_source_detail`.`id` =14");
        $this->execute("INSERT INTO `MunchAdo`.`point_source_detail` (`id` ,`name` ,`points` ,`csskey` ,`identifier` ,`created_at`) VALUES (
NULL , 'Try Food', '1', 'tryIcon active', 'tryFood', '2014-04-14 00:00:00'
);");
    }//up()

    public function down()
    {
    }//down()
}
