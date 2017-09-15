<?php

class AlterTableLogError extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `log_error` CHANGE  `message`  `message` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ;");
    }

    public function down() {
        
    }
}
