<?php

class Createtablesocialfeed extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$social_feed = $this->create_table("social_feeds");
    	$social_feed->column('feed_id', "string");
    	$social_feed->column('u_id', "integer");
    	$social_feed->column('text', "text");
    	$social_feed->column('u_name', "string");
    	$social_feed->column('u_handle', "string");
    	$social_feed->column('u_profile_image_url', "string");  
    	$social_feed->column('data', "text");	  	  	  	
    	$social_feed->finish();
    }//up()

    public function down()
    {
    	$this->drop_table("social_feeds");
    }//down()
}
