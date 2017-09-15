<?php

class CreatesumrizetableForUserRestaurantAndMenu extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$user_statistics = $this->create_table("user_statistics");

    		$user_statistics->column("user_id", "integer");
    		$user_statistics->column("my_points", "integer");
    		$user_statistics->column("order_count", "integer");
    		$user_statistics->column("reservation_count", "integer");
    		$user_statistics->column("deals_count", "integer");
    		$user_statistics->column("friends_count", "integer");
    		$user_statistics->column("bookmarks_count", "integer");
    		$user_statistics->column("reviews_count", "integer");
    		$user_statistics->column("groups_order_count", "integer");
    		$user_statistics->column("common_friends_count", "integer");
    		$user_statistics->finish();
////////////////////////////////////////////////////////////////////
    		$restaurant_statistics = $this->create_table("restaurant_statistics");

    		$restaurant_statistics->column("restaurant_id", "integer");
    		$restaurant_statistics->column("like_count", "integer");
    		$restaurant_statistics->column("love_count", "integer");
    		$restaurant_statistics->column("review_count", "integer");
    		$restaurant_statistics->finish();
////////////////////////////////////////////////////////////////////
    		$menu_statistics = $this->create_table("menu_statistics");

    		$menu_statistics->column("menu_id", "integer");
    		$menu_statistics->column("like_count", "integer");
    		$menu_statistics->column("love_count", "integer");
    		$menu_statistics->column("been_there_count", "integer");
    		$menu_statistics->column("want_it_count", "integer");
    		$menu_statistics->finish();

    }//up()

    public function down()
    {
    	$this->drop_table("user_statistics");
    	$this->drop_table("restaurant_statistics");
    	$this->drop_table("menu_statistics");
    }//down()
}
