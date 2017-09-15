<?php

class AddIndexToReportAbuseRestaurants extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `hungry_buzz`.`report_abuse_restaurants` 
						ADD UNIQUE INDEX `unique_index` (`user_id` ASC, `review_id` ASC, 
						`restaurant_id` ASC )");
    }//up()

    public function down()
    {
    }//down()
}
