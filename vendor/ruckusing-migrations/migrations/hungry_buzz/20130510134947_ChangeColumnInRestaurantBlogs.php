<?php

class ChangeColumnInRestaurantBlogs extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table restaurant_blogs change column image_id image_name varchar(100)");
    }//up()

    public function down()
    {
    }//down()
}
