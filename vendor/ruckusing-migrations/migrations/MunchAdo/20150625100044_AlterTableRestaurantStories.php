<?php

class AlterTableRestaurantStories extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("ALTER TABLE  `restaurant_stories` 
CHANGE  `chef_story_source`  `chef_story_source` VARCHAR( 2000 ), 
CHANGE  `chef_name`  `chef_name` VARCHAR( 2000 ),
CHANGE  `chef_story`  `chef_story` TEXT,
CHANGE  `restaurant_owner`  `restaurant_owner` VARCHAR( 2000 ),
CHANGE  `awards`  `awards` TEXT,
CHANGE  `shows`  `shows` TEXT,
CHANGE  `image`  `image` VARCHAR( 200 ),
CHANGE  `restaurant_desc`  `restaurant_desc` TEXT,
CHANGE  `restaurant_history`  `restaurant_history` TEXT,
CHANGE  `restaurant_history_source`  `restaurant_history_source` TEXT,
CHANGE  `other_info`  `other_info` TEXT,
CHANGE  `other_info_source`  `other_info_source` TEXT,
CHANGE  `final_story`  `final_story` TEXT,
CHANGE  `tag`  `tag` TEXT");
        
    }

    public function down() {
        
    }

}
