<?php

class UpdateRestaurantDetail extends Ruckusing_Migration_Base
{
    public function up()
    {
      $this->execute("ALTER TABLE `restaurants_details` ADD `is_chain` TINYINT(1) NULL DEFAULT '0'");
    }//up()

    public function down()
    {
    }//down()
}
