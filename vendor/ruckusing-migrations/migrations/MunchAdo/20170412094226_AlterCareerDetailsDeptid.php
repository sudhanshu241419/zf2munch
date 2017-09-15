<?php

class AlterCareerDetailsDeptid extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `career_details` CHANGE `platform` `platform` TINYINT NOT NULL DEFAULT '0' COMMENT '0=munchado,1=bravvura';");
        $this->execute("ALTER TABLE `career_details`  ADD `dept_id` INT NOT NULL AFTER `id`");
    }//up()

    public function down()
    {
    }//down()
}
