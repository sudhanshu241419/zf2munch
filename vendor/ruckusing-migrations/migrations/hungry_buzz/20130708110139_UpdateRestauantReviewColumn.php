<?php

class UpdateRestauantReviewColumn extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE restaurant_reviews DROP INDEX reviews");
    	$this->execute("ALTER TABLE `restaurant_reviews` CHANGE `reviews` `reviews` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");
    }//up()

    public function down()
    {
    }//down()
}
