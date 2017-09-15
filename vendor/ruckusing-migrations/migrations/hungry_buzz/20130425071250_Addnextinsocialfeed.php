<?php

class Addnextinsocialfeed extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("social_feeds", "next_link", "text");
    }//up()

    public function down()
    {
    	$this->remove_column("social_feeds", "next_link");
    }//down()
}
