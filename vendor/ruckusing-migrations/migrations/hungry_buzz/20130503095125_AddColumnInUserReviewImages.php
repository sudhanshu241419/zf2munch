<?php

class AddColumnInUserReviewImages extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table user_review_images add column order_id int(11) NOT NULL");
    }//up()

    public function down()
    {
    }//down()
}
