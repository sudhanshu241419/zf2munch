<?php

class UpdateTableUsers2 extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `users` ADD  `referral_ext` VARCHAR( 50 ) NULL DEFAULT NULL AFTER  `referral_code`;");
    }

    public function down() {
        
    }
}
