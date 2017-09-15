<?php

class CreateTableImportedContacts extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `user_imported_contactlist` (
						  `id` int(11) NOT NULL AUTO_INCREMENT,
						  `user_id` int(11) NOT NULL,
						  `contact_source` varchar(20) NOT NULL,
						  `contact_list` text NOT NULL,
						  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  PRIMARY KEY (`id`)
						) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
    }//up()

    public function down()
    {
    	$this->drop_table('user_imported_contactlist') ; 
    }//down()
}
