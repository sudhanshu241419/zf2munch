<?php

class AlterTableUserTipsandImage extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `user_tips` CHANGE `status` `status` ENUM('0','1','2') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '2'");
        $this->execute("ALTER TABLE  `user_restaurant_image` CHANGE  `status`  `status` TINYINT( 1 ) NOT NULL DEFAULT  '2'");
    }//up()

    public function down()
    {
    }//down()
}
