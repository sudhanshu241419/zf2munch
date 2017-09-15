<?php

class UpdateTableUsers extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `users` ADD  `referral_code` VARCHAR( 30 ) NULL DEFAULT NULL ;");
    }

    public function down() {
        
    }
}
