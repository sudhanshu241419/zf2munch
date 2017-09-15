<?php

class AddColumnInRestaurantsDetailS extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table restaurants_details add column grade_description varchar(255) after grade");
    }//up()

    public function down()
    {
    }//down()
}
