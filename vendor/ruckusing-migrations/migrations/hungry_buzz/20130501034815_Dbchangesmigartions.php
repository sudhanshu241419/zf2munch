<?php

class Dbchangesmigartions extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->drop_table('categories');
    	$this->drop_table('review_upload_pics');
    	$this->add_column('user_activities', "menu_id", "integer");
    	$this->rename_table('user_activities', 'user_activities_summary');
    	$this->rename_table('notification_types','user_settings');
    	$this->remove_column('restaurant_reviews', 'sentiments');
    	$this->execute("insert into features(features, feature_type, search_status) VALUES('sentiments', 'Ambience', 1)");
    	$this->add_column("restaurants", "ratings", "float");
    	$this->remove_column('restaurants', 'restaurant_facilities');
    	$this->remove_column('user_friends', 'updated_on');
    	$this->execute("Alter table user_searches change ip ip int(11)");
    	$this->add_column("restaurant_chefs", "city_id", "integer");
    	
    }//up()

    public function down()
    {
    	$this->remove_column("restaurant_chefs", "city_id");
    	$this->execute("Alter table user_searches change ip ip varchar(255)");
    	$this->add_column('user_friends', 'updated_on', 'datetime');
    	$this->add_column('restaurants', 'restaurant_facilities', 'string');
    	$this->remove_column("restaurants", "ratings");
    	$this->add_column('restaurant_reviews', 'sentiments', "string");
    	$this->rename_table('user_settings','notification_types');
    	$this->rename_table('user_activities_summary','user_activities');
    	$this->add_column('user_activities', "menu_id", "integer");
    }//down()
}
