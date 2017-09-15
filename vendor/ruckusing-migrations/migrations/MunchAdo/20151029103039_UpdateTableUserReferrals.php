<?php

class UpdateTableUserReferrals extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `user_referrals` ADD  `ref_amt_credited` TINYINT NOT NULL DEFAULT  '0' AFTER  `order_placed` ");
    }

    public function down() {
        
    }
}
