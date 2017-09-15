<?php

class AddUserPointsDescription extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column("user_points", "points_descriptions", "text");
    }//up()

    public function down()
    {
    	$this->remove_column("user_points", "points_descriptions");
    }//down()
}
