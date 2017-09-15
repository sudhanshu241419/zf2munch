<?php

class AlterCareerDetailsFieldmodify extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `career_details` CHANGE  `skills`  `the_ideal` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");
        $this->execute("ALTER TABLE  `career_details` CHANGE  `responsibilty`  `what_you_will_do` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");
        $this->execute("ALTER TABLE  `career_details` CHANGE  `should_have`  `what_you_will_need` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");
        $this->execute("ALTER TABLE  `career_details` DROP  `jd_name`");
    }//up()

    public function down()
    {
    }//down()
}