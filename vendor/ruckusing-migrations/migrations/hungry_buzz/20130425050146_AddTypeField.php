<?php

class AddTypeField extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("social_feeds", "type", "string");
    	$this->add_column("social_feeds", "posted_at", "datetime");
    	$this->add_column("social_feeds", "created_at", "datetime");

    }//up()

    public function down()
    {
    	$this->remove_column("social_feeds", "type", "string");
    	$this->remove_column("social_feeds", "posted_at", "datetime");
    	$this->remove_column("social_feeds", "created_at", "datetime");
    }//down()
}
