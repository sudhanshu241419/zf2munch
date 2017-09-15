<?php

class CreateTableAbandonedCart extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("CREATE TABLE `abandoned_cart` (
 `id` bigint(20) NOT NULL AUTO_INCREMENT,
 `cart_data` text COLLATE utf8_unicode_ci NOT NULL,
 `exception` text COLLATE utf8_unicode_ci NOT NULL,
 `origin` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
 `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 `status` enum('new','pending','closed','') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'new',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    }

    public function down() {
        
    }

}
