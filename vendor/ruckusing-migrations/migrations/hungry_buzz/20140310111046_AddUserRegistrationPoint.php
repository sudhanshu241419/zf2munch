<?php
class AddUserRegistrationPoint extends Ruckusing_Migration_Base {
	public function up() {
		$this->execute ( "INSERT INTO `point_source_detail_new` (`id` ,`name` ,`points` ,`csskey` ,`created_at`)VALUES (NULL , 'New Registration', '250', 'newUser', '2014-03-10 00:00:00');" );
	} // up()
	public function down() {
	} // down()
}
