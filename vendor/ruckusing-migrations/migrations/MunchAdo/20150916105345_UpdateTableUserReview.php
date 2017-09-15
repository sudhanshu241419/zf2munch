<?php

class UpdateTableUserReview extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute('UPDATE `user_reviews` set `approved_date`=created_on;');
    }//up()

    public function down()
    {
    }//down()
}
