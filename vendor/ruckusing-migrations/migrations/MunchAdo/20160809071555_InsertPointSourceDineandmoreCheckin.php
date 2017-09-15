<?php

class InsertPointSourceDineandmoreCheckin extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO  `MunchAdo`.`point_source_detail` (`id` ,`name` ,`points` ,`csskey` ,`identifier` ,`created_at` ,`points_for` ,`dindex` ,`dstatus`) VALUES ('0',  'Dine & more awards checkin',  '25',  'checkIn',  'awardscheckin',  '2016-08-09 06:19:22',  'ap',  '4',  '1')");
        $this->execute("INSERT INTO  `MunchAdo`.`point_source_detail` (`id` ,`name` ,`points` ,`csskey` ,`identifier` ,`created_at` ,`points_for` ,`dindex` ,`dstatus`) VALUES ('0',  'Dine & more awards checkin with photo',  '35',  'checkIn',  'awardscheckinphoto',  '2016-08-09 06:19:22',  'ap',  '4',  '1')");
        $this->execute("INSERT INTO  `MunchAdo`.`point_source_detail` (`id` ,`name` ,`points` ,`csskey` ,`identifier` ,`created_at` ,`points_for` ,`dindex` ,`dstatus`) VALUES ('0',  'Dine & more awards checkin with friend',  '30',  'checkIn',  'awardscheckinfriend',  '2016-08-09 06:19:22',  'ap',  '4',  '1')");
        $this->execute("INSERT INTO  `MunchAdo`.`point_source_detail` (`id` ,`name` ,`points` ,`csskey` ,`identifier` ,`created_at` ,`points_for` ,`dindex` ,`dstatus`) VALUES ('0',  'Dine & more awards checkin with menu',  '30',  'checkIn',  'awardscheckinmenu',  '2016-08-09 06:19:22',  'ap',  '4',  '1')");
        $this->execute("INSERT INTO  `MunchAdo`.`point_source_detail` (`id` ,`name` ,`points` ,`csskey` ,`identifier` ,`created_at` ,`points_for` ,`dindex` ,`dstatus`) VALUES ('0',  'Dine & more awards checkin with menu photo',  '35',  'checkIn',  'awardscheckinmenuphoto',  '2016-08-09 06:19:22',  'ap',  '4',  '1')");
        $this->execute("INSERT INTO  `MunchAdo`.`point_source_detail` (`id` ,`name` ,`points` ,`csskey` ,`identifier` ,`created_at` ,`points_for` ,`dindex` ,`dstatus`) VALUES ('0',  'Dine & more awards checkin with menu friend',  '30',  'checkIn',  'awardscheckinmenufriend',  '2016-08-09 06:19:22',  'ap',  '4',  '1')");
        $this->execute("INSERT INTO  `MunchAdo`.`point_source_detail` (`id` ,`name` ,`points` ,`csskey` ,`identifier` ,`created_at` ,`points_for` ,`dindex` ,`dstatus`) VALUES ('0',  'Dine & more awards checkin with photo friend',  '35',  'checkIn',  'awardscheckinphotofriend',  '2016-08-09 06:19:22',  'ap',  '4',  '1')");
        $this->execute("INSERT INTO  `MunchAdo`.`point_source_detail` (`id` ,`name` ,`points` ,`csskey` ,`identifier` ,`created_at` ,`points_for` ,`dindex` ,`dstatus`) VALUES ('0',  'Dine & more awards checkin with menu photo friend',  '35',  'checkIn',  'awardscheckinmenuphotofriend',  '2016-08-09 06:19:22',  'ap',  '4',  '1')");
        
    }//up()

    public function down()
    {
    }//down()
}
