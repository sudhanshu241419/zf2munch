<?php

class UpdateTableTags extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `tags` ADD  `status` TINYINT NOT NULL DEFAULT  '1'");
    }

    public function down() {
        
    }
}
