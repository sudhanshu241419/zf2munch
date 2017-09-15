<?php

class ChangeCreatedOnToAt extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->rename_column("restaurant_deals","created_on", "created_at");
      $this->rename_column("restaurant_deals","updated_on", "updated_at");
      $this->rename_column("restaurants","created_on", "created_at");
    	$this->rename_column("restaurants","updated_on", "updated_at");
      $this->rename_column("menues","created_on", "created_at");
      $this->rename_column("chefs","created_on", "created_at");
      $this->rename_column("cuisines","created_on", "created_at");
    	$this->rename_column("restaurant_chefs","created_on", "created_at");

    	$this->rename_column("restaurant_accounts","created_on", "created_at");
    	$this->rename_column("restaurant_accounts","updated_on", "updated_at");

    	$this->rename_column("restaurant_coupons","created_on", "created_at");
    	$this->rename_column("restaurant_coupons","updated_on", "updated_at");

    	$this->rename_column("restaurant_cuisines","created_on", "created_at");

    	$this->rename_column("restaurant_rattings","created_on", "created_at");

    	$this->rename_column("restaurant_videos","created_on", "created_at");
    	$this->rename_column("restaurant_videos","updated_on", "updated_at");

    	$this->rename_column("review_upload_pics","created_on", "created_at");

    	$this->rename_column("user_activities","created_on", "created_at");

    	$this->rename_column("user_cards","created_on", "created_at");
    	$this->rename_column("user_cards","updated_on", "updated_at");
 
    	$this->rename_column("user_followers","created_on", "created_at");

    	$this->rename_column("user_orders","created_on", "created_at");
    	$this->rename_column("user_orders","updated_on", "updated_at");

    	$this->rename_column("user_points","created_on", "created_at");

    	$this->rename_column("user_reviews","created_on", "created_at");

    	$this->rename_column("user_shares","shared_on", "shared_at");

    }//up()

    public function down()
    {
    	$this->rename_column("restaurant_deals","created_at", "created_on");
    	$this->rename_column("restaurant_deals","updated_at", "updated_on");

    	$this->rename_column("restaurants","created_at", "created_on");
    	$this->rename_column("restaurants","updated_at", "updated_on");

    	$this->rename_column("restaurant_accounts","created_at", "created_on");
    	$this->rename_column("restaurant_accounts","updated_at", "updated_on");

    	$this->rename_column("menues","created_at", "created_on");

    	$this->rename_column("chefs","created_at", "created_on");

    	$this->rename_column("restaurant_chefs","created_at", "created_on");

    	$this->rename_column("cuisines","created_on", "created_on");

    	$this->rename_column("restaurant_coupons","created_at", "created_on");
    	$this->rename_column("restaurant_coupons","updated_at", "updated_on");

    	$this->rename_column("restaurant_cuisines","created_on", "created_on");

    	$this->rename_column("restaurant_rattings","created_on", "created_on");

    	$this->rename_column("restaurant_videos","created_at", "created_on");
    	$this->rename_column("restaurant_videos","updated_at", "updated_on");

    	$this->rename_column("review_upload_pics","created_at", "created_on");

    	$this->rename_column("user_activities","created_at", "created_on");

    	$this->rename_column("user_cards","created_at", "created_on");
    	$this->rename_column("user_cards","updated_at", "updated_on");

    	$this->rename_column("user_followers","created_at", "created_on");

    	$this->rename_column("user_orders","created_at", "created_on");
    	$this->rename_column("user_orders","updated_at", "updated_on");

    	$this->rename_column("user_points","created_at", "created_on");

    	$this->rename_column("user_reviews","created_at", "created_on");

    	$this->rename_column("user_shares","shared_at", "shared_on");
    }//down()
}
