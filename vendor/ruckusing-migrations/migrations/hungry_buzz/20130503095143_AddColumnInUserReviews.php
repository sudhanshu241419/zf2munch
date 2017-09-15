<?php

class AddColumnInUserReviews extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table user_reviews add column order_id int(11) NOT NULL");
    }//up()

    public function down()
    {
    }//down()
}
