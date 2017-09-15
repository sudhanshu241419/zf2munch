<?php

class AddColumnRestaurantIdInMerchantRegistration extends Ruckusing_Migration_Base
{
    public function up()
    {
    	/*$this->execute("ALTER TABLE `merchant_registration` ADD `restaurant_id` INT NULL AFTER `id`");*/
    }//up()

    public function down()
    {
    }//down()
}
