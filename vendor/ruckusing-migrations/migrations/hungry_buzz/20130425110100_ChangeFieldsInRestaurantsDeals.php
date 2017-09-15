<?php

class ChangeFieldsInRestaurantsDeals extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE restaurant_deals CHANGE deal_image_id image varchar(200)");
    	$this->add_column("restaurant_deals", "trend", "boolean");
    }//up()

    public function down()
    {
    	$this->execute("ALTER TABLE restaurant_deals CHANGE image deal_image_id int(5)");
    	$this->add_column("restaurant_deals", "trend");
    }//down()
}
