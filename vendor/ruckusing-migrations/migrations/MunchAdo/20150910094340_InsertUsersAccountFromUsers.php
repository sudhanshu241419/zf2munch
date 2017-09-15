<?php

class InsertUsersAccountFromUsers extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO  `user_account` (  `user_id` ,  `user_name` ,  `first_name` ,  `last_name` ,  `user_source` ,  `display_pic_url` ,  `display_pic_url_normal` , `display_pic_url_large` ,  `session_token` ,  `access_token` ) 
SELECT  `id` ,  `user_name` ,  `first_name` ,  `last_name` ,  `user_source` ,  `display_pic_url` ,  `display_pic_url_normal` ,  `display_pic_url_large` ,  `session_token` , `access_token` FROM  `users");
   
    }//up()

    public function down()
    {
    }//down()
}
