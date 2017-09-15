<?php

class CreateTableUserAvatar extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `user_avatar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `avatar_type` varchar(255) NOT NULL,
  `action_count` tinyint(4) NOT NULL,
  `date_earned` datetime NOT NULL,
  `is_earned` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1=>earned,0=> not earned',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=>active,0=>disabled/deleted',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
    }//up()

    public function down()
    {
    }//down()
}
