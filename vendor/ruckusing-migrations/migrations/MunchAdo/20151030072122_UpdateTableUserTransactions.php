<?php

class UpdateTableUserTransactions extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `user_transactions` ADD  `category` TINYINT NOT NULL DEFAULT  '1' COMMENT  '1=default,2=referral_credit,3=redemption' AFTER `transaction_amount`");
        $this->execute("UPDATE user_transactions SET category = 2 WHERE  `transaction_type` =  'credit' AND  `remark` LIKE  '%against referred users%'");
    }

    public function down() {
        
    }
}
