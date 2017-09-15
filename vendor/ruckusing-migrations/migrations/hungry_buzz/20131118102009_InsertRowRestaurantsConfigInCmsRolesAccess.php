<?php

class InsertRowRestaurantsConfigInCmsRolesAccess extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("INSERT INTO `hungry_buzz`.`cms_roles_access` (
			`id` ,
			`role_id` ,
			`module_id` ,
			`module_create` ,
			`module_update` ,
			`module_view` ,
			`module_delete`
			)
			VALUES (
			NULL , '1', '58', '1', '1', '1', '1'
			);");
    }//up()

    public function down()
    {
    }//down()
}
