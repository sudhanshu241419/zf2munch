<?php

class AlterPromocodeTableForPromocodeType extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `promocodes` ADD  `promocodeType` TINYINT NOT NULL COMMENT  '1=normal, 2=promotional' AFTER  `end_date`");
    }//up()

    public function down()
    {
    }//down()
}
