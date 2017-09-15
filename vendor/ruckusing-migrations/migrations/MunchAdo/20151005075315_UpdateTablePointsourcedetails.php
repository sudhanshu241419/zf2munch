<?php

class UpdateTablePointsourcedetails extends Ruckusing_Migration_Base
{
    public function up()
    {
        //$this->execute("ALTER TABLE  `point_source_detail` ADD  `points_for` ENUM('ws', 'bt', 'ap' ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT  'ws => website, bt => both, ap=> mobile App';");
    }//up()

    public function down()
    {
    }//down()
}
