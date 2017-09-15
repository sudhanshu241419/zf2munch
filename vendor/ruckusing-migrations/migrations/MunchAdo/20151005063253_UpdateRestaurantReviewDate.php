<?php

class UpdateRestaurantReviewDate extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE restaurant_reviews SET  `date` = CURDATE( ) - INTERVAL 10 DAY WHERE `date` =  '0000-00-00'");
        $this->execute("UPDATE restaurant_reviews SET `date` = CONCAT(  '2005', RIGHT( DATE, 6 ) ) WHERE `date` <  '2001-01-01'");
        $this->execute("UPDATE restaurant_reviews SET `date` = CONCAT(  '2014', RIGHT( DATE, 6 ) ) WHERE `date` >  '2015-10-05'");
    }//up()

    public function down()
    {
    }//down()
}
