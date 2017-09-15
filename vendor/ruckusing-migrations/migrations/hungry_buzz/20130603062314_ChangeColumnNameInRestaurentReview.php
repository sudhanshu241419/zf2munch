<?php

class ChangeColumnNameInRestaurentReview extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->remove_column("restaurant_reviews_temp", "consolidated_reviews");
    	$this->remove_column("restaurant_reviews_raw", "consolidated_reviews");
    }//up()

    public function down()
    {
    	$this->add_column("restaurant_reviews_temp", "consolidated_reviews", "string");
    	$this->add_column("restaurant_reviews_raw", "consolidated_reviews", "string");
    	
    }//down()
}
