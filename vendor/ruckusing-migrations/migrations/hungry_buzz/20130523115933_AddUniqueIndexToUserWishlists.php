<?php

class AddUniqueIndexToUserWishlists extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `hungry_buzz`.`user_wishlists` 
						ADD UNIQUE INDEX `unique_index` (`user_id` ASC, 
						`restaurant_id` ASC, `review_id` ASC)");
    }//up()

    public function down()
    {
    }//down()
}
