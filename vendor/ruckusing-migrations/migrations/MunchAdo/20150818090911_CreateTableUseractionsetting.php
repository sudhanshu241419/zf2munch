<?php

class CreateTableUseractionsetting extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `user_action_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order` tinyint(4) NOT NULL COMMENT '1=allow order',
  `reservation` tinyint(4) NOT NULL COMMENT '1=allow reservation',
  `bookmarks` tinyint(4) NOT NULL COMMENT '1=allow bookmark',
  `checkin` tinyint(4) NOT NULL COMMENT '1=allow checkin',
  `muncher_unlocked` tinyint(4) NOT NULL COMMENT '1=allow unlock muncher',
  `upload_photo` tinyint(4) NOT NULL COMMENT '1=allow upload photo',
  `reviews` tinyint(4) NOT NULL COMMENT '1=allow review',
  `tips` tinyint(4) NOT NULL COMMENT '1=allow tips',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");
    }//up()

    public function down()
    {
    }//down()
}
