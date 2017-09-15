<?php

class CreateTableUserTransactions extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("CREATE TABLE `user_transactions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `transaction_type` enum('credit','debit') COLLATE utf8_unicode_ci NOT NULL,
            `transaction_amount` float(10,2) NOT NULL,
            `transaction_date` datetime NOT NULL,
            `remark` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
            PRIMARY KEY (`id`)
           ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    }

    public function down() {
        
    }
}
