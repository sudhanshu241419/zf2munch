<?php

class CreatePointSourcedetailstructure extends Ruckusing_Migration_Base
{
    public function up()
    {
      $this->execute("drop table point_source_detail");  
      $this->execute("CREATE TABLE IF NOT EXISTS `point_source_detail` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `points` int(11) NOT NULL,
      `csskey` varchar(60) NOT NULL,
      `identifier` varchar(30) NOT NULL,
      `created_at` datetime DEFAULT NULL,
      `points_for` enum('ws','bt','ap') DEFAULT NULL COMMENT 'ws => website, bt => both, ap=> mobile App',
      `dindex` int(11) NOT NULL,
      `dstatus` enum('0','1') NOT NULL DEFAULT '1' COMMENT '0=>Inactive, 1=> Active',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='User Points Master Table' AUTO_INCREMENT=1");

    }//up()

    public function down()
    {
    }//down()
}
