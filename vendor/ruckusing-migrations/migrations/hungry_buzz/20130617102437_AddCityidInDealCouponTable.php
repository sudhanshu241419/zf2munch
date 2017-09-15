<?php

class AddCityidInDealCouponTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `restaurant_deals` ADD `city_id` INT NOT NULL AFTER `restaurant_id`");
    	$this->execute("ALTER TABLE `restaurant_coupons` ADD `city_id` INT NOT NULL AFTER `restaurant_id`");
    	$this->execute("UPDATE restaurant_deals d JOIN  restaurants r ON (d.restaurant_id = r.id) SET d.city_id = r.city_id ");
    	$this->execute("UPDATE restaurant_coupons d JOIN  restaurants r ON (d.restaurant_id = r.id) SET d.city_id = r.city_id ");
    }//up()

    public function down()
    {
    	$this->remove_column('restaurant_deals','city_id');
    	$this->remove_column('restaurant_coupons','city_id');
    }//down()
}
