<?php

class InsertDataPointSourceDetail extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO  `MunchAdo`.`point_source_detail` (
`id` ,
`name` ,
`points` ,
`csskey` ,
`identifier` ,
`created_at`
)
VALUES (
'0',  'Upload Restaurant Photo',  '50',  'twitterIconPoint',  'uploadRestaurantPhoto',  '2013-06-14 06:19:22'
);");
    }//up()

    public function down()
    {
    }//down()
}
