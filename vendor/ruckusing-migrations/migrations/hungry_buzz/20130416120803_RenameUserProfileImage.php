<?php

class RenameUserProfileImage extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->rename_column("users","profile_image", "display_pic_url");
    }//up()

    public function down()
    {
    	$this->rename_column("users", "display_pic_url", "profile_image");
    }//down()
}
