<?php
class CreateUserFeedbackTable extends Ruckusing_Migration_Base {
	public function up() {
		$this->execute ( "CREATE TABLE IF NOT EXISTS `user_feedback` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `review_id` int(11) NOT NULL,
				  `user_id` int(11) NOT NULL,
				  `feedback` tinyint(1) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 " 
		);
	} // up()
	public function down() {
	} // down()
}
