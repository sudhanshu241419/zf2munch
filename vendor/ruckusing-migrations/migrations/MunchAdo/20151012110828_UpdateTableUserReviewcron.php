<?php

class UpdateTableUserReviewcron extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_reviews` ADD `cronUpdate` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `assignMuncher` ;");
    }//up()

    public function down()
    {
    }//down()
}
