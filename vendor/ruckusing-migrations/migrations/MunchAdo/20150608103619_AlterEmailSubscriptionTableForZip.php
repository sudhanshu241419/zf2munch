<?php

class AlterEmailSubscriptionTableForZip extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `email_subscription` ADD `zip` INT NOT NULL AFTER `email` ;");
    }//up()

    public function down()
    {
    }//down()
}
