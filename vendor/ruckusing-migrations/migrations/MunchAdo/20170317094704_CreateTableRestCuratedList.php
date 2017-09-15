<?php

class CreateTableRestCuratedList extends Ruckusing_Migration_Base
{
    public function up()
    {
         $this->execute("CREATE TABLE IF NOT EXISTS `restaurant_curated_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phrase` varchar(256) NOT NULL,
  `restaurant_ids` text NOT NULL, 
  `food_items` text NOT NULL, 
  `cuisine` text NOT NULL, 
  `type_of_place` text NOT NULL, 
  `curated_image` varchar(255) NOT NULL,
  `curated_video` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=>active,0=>inactive',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
    }//up()

    public function down()
    {
    }//down()
}
