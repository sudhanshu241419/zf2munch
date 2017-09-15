<?php

class CreateTableActivityFeed extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `activity_feed` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `feed_type` int(11) NOT NULL,
        `user_id` int(11) NOT NULL,
        `feed` tinytext NOT NULL,
        `feed_for_others` tinytext NOT NULL,
        `event_date_time` datetime NOT NULL,
        `added_date_time` datetime NOT NULL,
        `status` tinyint(1) NOT NULL COMMENT '1=>active, 2=>archive',
        PRIMARY KEY (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
    }//up()

    public function down()
    {
    }//down()
}
