<?php

class ModifyReviewTypeFieldRestaurantsReviews extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `restaurant_reviews` CHANGE `review_type` `review_type` VARCHAR( 200 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'N'");
    }//up()

    public function down()
    {
    }//down()
}
