<?php

class CreateTableUserReferrals extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("CREATE TABLE IF NOT EXISTS `user_referrals` (
            `user_id` int(11) NOT NULL,
            `inviter_id` int(11) NOT NULL,
            `order_placed` tinyint(4) NOT NULL DEFAULT '0',
            `updated_on` datetime NOT NULL,
            PRIMARY KEY (`user_id`),
            UNIQUE KEY `user_id` (`user_id`),
            KEY `inviter_id` (`inviter_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    }

    public function down() {
        
    }
}
