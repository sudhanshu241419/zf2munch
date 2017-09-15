<?php

class UpdateTablePoint extends Ruckusing_Migration_Base
{
    public function up()
    {
     $this->execute("UPDATE `point_source_detail` SET `identifier` = 'checkMenu' WHERE `point_source_detail`.`id` =25;");
     $this->execute("UPDATE `point_source_detail` SET `identifier` = 'checkPhoto' WHERE `point_source_detail`.`id` =26;");
     $this->execute("UPDATE `point_source_detail` SET `identifier` = 'checkFriend' WHERE `point_source_detail`.`id` =27;");
     $this->execute("UPDATE `point_source_detail` SET `identifier` = 'checkMenuPhoto' WHERE `point_source_detail`.`id` =28;");
     $this->execute("UPDATE `point_source_detail` SET `identifier` = 'checkMenuFriend' WHERE `point_source_detail`.`id` =29;");
     $this->execute("UPDATE `point_source_detail` SET `identifier` = 'checkMenuPhotoFriend' WHERE `point_source_detail`.`id` =30;");
    }//up()

    public function down()
    {
    }//down()
}
