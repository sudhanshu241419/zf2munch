<?php

class Alterdatetimefields extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table users change updated_at update_at datetime");
    	$this->execute("alter table user_wishlists change created_on created_on datetime");
    	$this->execute("alter table user_shares change shared_on shared_on datetime");
    	$this->execute("alter table user_searches change searched_on searched_on datetime");
    	$this->execute("alter table user_reviews change created_on created_on datetime");
    	$this->execute("alter table user_review_images change created_at created_at datetime");
    	$this->execute("alter table user_reset_password change created_on created_on datetime");
    	$this->execute("alter table user_points change created_on created_at datetime");
    	$this->execute("alter table user_point_redeemed change redeemed_on redeemed_on datetime");
    	$this->execute("alter table user_point_redeemed change redeemed_on redeemed_on datetime");
    	$this->execute("alter table user_orders change updated_at updated_at datetime");
    	$this->execute("alter table user_orders change updated_at updated_at datetime");
    	$this->execute("alter table user_imported_contactlist change create_at create_at datetime");
    	$this->execute("alter table user_followers change created_on created_on datetime");
    	$this->execute("alter table user_favorites change updated_on updated_at datetime");
    	$this->execute("alter table user_cards change updated_on updated_at datetime");
    	$this->execute("alter table user_addresses change updated_on updated_at datetime");
    	$this->execute("alter table user_activities_summary change created_on created_on datetime");
    	$this->execute("alter table site_settings change restaurant_order_frozen_time restaurant_order_frozen_time datetime");
    	$this->execute("alter table site_contents change created_on created_on datetime");
    	$this->execute("alter table site_banners change updated_on updated_at datetime");
    	$this->execute("alter table site_administrators change created_on created_on datetime");

    }//up()

    public function down()
    {
    	
    }//down()
}
