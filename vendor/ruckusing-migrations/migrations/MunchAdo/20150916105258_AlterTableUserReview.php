<?php

class AlterTableUserReview extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute('ALTER TABLE `user_reviews` ADD `approved_date` DATETIME NULL AFTER `approved_by`;');
    }//up()

    public function down()
    {
    }//down()
}
