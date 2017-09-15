<?php

class AlterCmsRoleAccessForModuleReassign extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE  `cms_roles_access` ADD  `module_reassign` TINYINT( 1 ) NOT NULL DEFAULT  '0'");
    }//up()

    public function down()
    {
    }//down()
}
