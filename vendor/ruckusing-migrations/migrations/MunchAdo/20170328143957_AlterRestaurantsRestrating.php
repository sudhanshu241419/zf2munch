<?php

class AlterRestaurantsRestrating extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `restaurants` ADD  `restaurant_rating` FLOAT NOT NULL DEFAULT  '0' AFTER  `cod`");
    }//up()

    public function down()
    {
    }//down()
}
