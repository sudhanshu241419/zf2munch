<?php

class UpdateRestaurantsBorough extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE `restaurants` SET borough='Manhattan' WHERE city_id='18848'");
    }//up()

    public function down()
    {
    }//down()
}
