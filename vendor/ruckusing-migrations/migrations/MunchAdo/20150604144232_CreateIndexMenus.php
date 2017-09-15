<?php

class CreateIndexMenus extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("CREATE INDEX ind_pid ON menus (pid)");
        $this->execute("CREATE INDEX ind_restaurant_id ON menus (restaurant_id)");
    }

    public function down() {
        
    }
}
