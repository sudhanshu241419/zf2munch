<?php

class AlterTableMenus extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `menus` ADD  `item_rank` TINYINT NOT NULL DEFAULT  '100' AFTER  `item_name` ;");
        $this->execute("ALTER TABLE  `menus` CHANGE  `status`  `status` TINYINT( 1 ) NOT NULL DEFAULT  '1';");
    }

    public function down() {
        
    }
}
