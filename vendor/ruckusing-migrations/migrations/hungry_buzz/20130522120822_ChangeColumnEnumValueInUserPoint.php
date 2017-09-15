<?php

class ChangeColumnEnumValueInUserPoint extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_points` CHANGE `point_source` `point_source` ENUM('se','ov','ss','1','2','3') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'se=>share email, ov=>order value, ss=>social share,1=>order,2=>reservation,3=>deals'");
    }//up()

    public function down()
    {
    	$this->execute("ALTER TABLE `user_points` CHANGE `point_source` `point_source` ENUM('se','ov','ss') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'se=>share email, ov=>order value, ss=>social share'");
    }//down()
}
