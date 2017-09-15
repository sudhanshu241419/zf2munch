<?php

class AddKeyInPointSource extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `point_source_detail` ADD `identifier` VARCHAR( 30 ) NULL DEFAULT NULL AFTER `csskey`");
    }//up()

    public function down()
    {
    }//down()
}
