<?php

class AlterTableUserOrders extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `user_orders` ADD  `efax_sent` TINYINT( 1 ) NOT NULL DEFAULT  '0'");
    }

    public function down() {
        
    }
}
