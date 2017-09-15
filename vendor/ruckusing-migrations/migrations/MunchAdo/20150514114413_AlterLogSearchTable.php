<?php

class AlterLogSearchTable extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE log_search MODIFY ip int(10) UNSIGNED;");
    }

    public function down() {
        
    }

}
