<?php

class ChangeResraurentReviewTable extends Ruckusing_Migration_Base
{
  
    public function up()
    {
    	/*$this->drop_table('restaurant_reviews');
    	$this->rename_table('restaurant_reviews_new','restaurant_reviews');
    	$this->execute('CREATE TABLE IF NOT EXISTS bookmarks (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  entity_id int(11) DEFAULT NULL,
  entity_type varchar(255) DEFAULT NULL,
  user_id int(11) DEFAULT NULL,
  date_time datetime DEFAULT NULL,
  loved_it tinyint(1) DEFAULT NULL,
  tired_it tinyint(1) DEFAULT NULL,
  bean_there varchar(255) DEFAULT NULL,
  want_it tinyint(1) DEFAULT NULL,
  liked_it tinyint(1) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY IDX_userid_entity_id (user_id,entity_id)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1') ;*/

    }//up()

    public function down()
    {
    	$this->rename_table('restaurant_reviews','restaurant_reviews_new');
    	$this->drop_table('bookmarks');
    }//down()
}
