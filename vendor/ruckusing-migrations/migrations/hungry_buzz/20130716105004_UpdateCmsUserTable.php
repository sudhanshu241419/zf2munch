<?php

class UpdateCmsUserTable extends Ruckusing_Migration_Base
{
    public function up()
    {
     $this->execute('ALTER TABLE `cms_users` ADD `role_id` int(11) NULL AFTER `superuser`');    
    }//up()

    public function down()
    {
    	$this->remove_column("cms_users","role_id");
    }//down()
}
