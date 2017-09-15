<?php

class UpdateTableRestaurants extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("UPDATE `restaurants` SET `allowed_zip`= '10001,10011' WHERE city_id = 18848 AND `landmark` LIKE 'Chelsea'");
        $this->execute("UPDATE `restaurants` SET `allowed_zip`= '10018,10019,10036' WHERE city_id = 18848 AND `landmark` LIKE \"Hell's Kitchen\"");
        $this->execute("UPDATE `restaurants` SET `allowed_zip`= '10016,10017,10022' WHERE city_id = 18848 AND `landmark` LIKE 'Midtown'");
        $this->execute("UPDATE `restaurants` SET `allowed_zip`= '10014' WHERE city_id = 18848 AND `landmark` LIKE 'West Village'");
        $this->execute("UPDATE `restaurants` SET `allowed_zip`= '10003,10009' WHERE city_id = 18848 AND `landmark` LIKE 'East Village'");
        $this->execute("UPDATE `restaurants` SET `allowed_zip`= '10023,10024,10025,10069' WHERE city_id = 18848 AND `landmark` LIKE 'Upper West Side'");
    }

    public function down() {
        
    }
}
