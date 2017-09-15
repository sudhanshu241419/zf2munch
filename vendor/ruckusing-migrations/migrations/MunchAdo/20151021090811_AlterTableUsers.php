<?php

class AlterTableUsers extends Ruckusing_Migration_Base {

    public function up() {
       $this->execute("ALTER TABLE  `users` ADD  `wallet_balance` DECIMAL(10,2) NOT NULL;");
    }

    public function down() {
        
    }
}
