<?php

class Addurlfieldinsocialfeed extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("social_feeds", "image_url", "string");
    }//up()

    public function down()
    {
    	$this->remove_column("social_feeds", "image_url");
    }//down()
}
