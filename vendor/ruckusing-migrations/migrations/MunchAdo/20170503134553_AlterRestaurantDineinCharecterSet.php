<?php

class AlterRestaurantDineinCharecterSet extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurant_dinein` CHANGE `booking_id` `booking_id` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `restaurant_name` `restaurant_name` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `first_name` `first_name` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `last_name` `last_name` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `email` `email` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `phone` `phone` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `user_instruction` `user_instruction` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `restaurant_offer` `restaurant_offer` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `restaurant_instruction` `restaurant_instruction` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, CHANGE `host_name` `host_name` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL, CHANGE `user_ip` `user_ip` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
    }//up()

    public function down()
    {
    }//down()
}
