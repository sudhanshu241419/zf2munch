<?php

class UpdateUserTableForLocation extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `users` ADD `city_id` INT NULL AFTER `phone` ;");
    }//up()

    public function down()
    {
    }//down()
}
