<?php

class AlterTableLogSearch extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `log_search` ADD  `filters` VARCHAR( 255 ) NULL, ADD  `where` VARCHAR( 255 ) NULL");
    }

    public function down() {
        
    }
}
