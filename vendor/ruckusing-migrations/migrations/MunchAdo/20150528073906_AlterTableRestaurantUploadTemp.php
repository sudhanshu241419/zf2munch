<?php

class AlterTableRestaurantUploadTemp extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE restaurant_upload_temp ADD content_status TINYINT(1) NOT NULL DEFAULT '1' COMMENT '1=>pending,2=>complete' AFTER status");
    }

    public function down() {
        
    }
}
