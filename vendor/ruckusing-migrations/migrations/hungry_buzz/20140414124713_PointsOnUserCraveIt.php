<?php
class PointsOnUserCraveIt extends Ruckusing_Migration_Base {
	public function up() {
		$this->execute ( "INSERT INTO `hungry_buzz`.`point_source_detail_new` (`id` ,`name` ,`points` ,`csskey` ,`created_at`)VALUES (NULL , 'User Bookmark', '1', 'bookmarked', '2014-04-14 00:00:00')" );
	} // up()
	public function down() {
	} // down()
}
