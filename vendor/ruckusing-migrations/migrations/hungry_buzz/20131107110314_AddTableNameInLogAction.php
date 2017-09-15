<?php

class AddTableNameInLogAction extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `log_action` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `action_name` varchar(100) CHARACTER SET latin1 NOT NULL,
		  `isactive` enum('1','0') CHARACTER SET latin1 NOT NULL DEFAULT '1',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;");

		$this->execute("INSERT INTO `log_action` (`id`, `action_name`, `isactive`) VALUES
			(1, 'insert', '1'),
			(2, 'update', '1'),
			(3, 'delete', '1'),
			(4, 'login', '1'),
			(5, 'logout', '1'),
			(6, 'upload', '1');");
		$this->execute("CREATE TABLE IF NOT EXISTS `user_log` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `user_id` int(11) NOT NULL,
			  `module_id` int(11) NOT NULL,
			  `table_id` int(11) NOT NULL,
			  `tablename` varchar(100) CHARACTER SET latin1 NOT NULL,
			  `action_id` int(11) NOT NULL,
			  `action_data` text CHARACTER SET latin1 NOT NULL,
			  `log_date` datetime NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");		
    }//up()

    public function down()
    {
    }//down()
}
