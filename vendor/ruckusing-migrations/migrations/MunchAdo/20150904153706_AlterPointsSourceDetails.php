<?php

class AlterPointsSourceDetails extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO  `point_source_detail` (`id`, `name`, `points`, `csskey`, `identifier`, `created_at`) VALUES (NULL, 'Let us know about secret menu items on the down low', '25', 'i_right_rightMark', 'i_right_rightMark', '2015-09-04 00:00:00'), (NULL, 'Let us know about a missing menu item', '10', 'i_right_rightMark', 'i_right_rightMark', '2015-09-04 06:00:00');");
        $this->execute("INSERT INTO  `point_source_detail` (`id`, `name`, `points`, `csskey`, `identifier`, `created_at`) VALUES (NULL, 'Email us about new, up-and-coming local chefs', '100', 'i_right_rightMark', 'i_right_rightMark', '2015-09-04 00:00:00'), (NULL, 'Correct a major mistake on MunchAdo.com', '100', 'i_right_rightMark', 'i_right_rightMark', '2015-09-04 06:00:00');");
        $this->execute("INSERT INTO  `point_source_detail` (`id`, `name`, `points`, `csskey`, `identifier`, `created_at`) VALUES (NULL, 'Tell us everything about a local restaurant we don\'t know', '250', 'i_right_rightMark', 'i_right_rightMark', '2015-09-04 00:00:00');");
        $this->execute("UPDATE  `point_source_detail` SET `name` = 'Leave a tip at restaurants you''ve tried.' WHERE `point_source_detail`.`id` =32;");
        $this->execute("UPDATE  `point_source_detail` SET `identifier` = 'checkIn' WHERE `point_source_detail`.`id` =25;");
        $this->execute("UPDATE  `point_source_detail` SET `identifier` = 'checkIn' WHERE `point_source_detail`.`id` =26;");
        $this->execute("UPDATE  `point_source_detail` SET `identifier` = 'checkIn' WHERE `point_source_detail`.`id` =27;");
        $this->execute("UPDATE  `point_source_detail` SET `identifier` = 'checkIn' WHERE `point_source_detail`.`id` =28;");
        $this->execute("UPDATE  `point_source_detail` SET `identifier` = 'checkIn' WHERE `point_source_detail`.`id` =29;");
        $this->execute("UPDATE  `point_source_detail` SET `identifier` = 'checkIn' WHERE `point_source_detail`.`id` =30;");
        $this->execute("UPDATE  `point_source_detail` SET `name` = 'Add a restaurant to your crave list' WHERE `point_source_detail`.`id` =21;");
        $this->execute("UPDATE  `point_source_detail` SET `name` = 'Mark as been there' WHERE `point_source_detail`.`id` =20;");
        $this->execute("UPDATE  `point_source_detail` SET `name` = 'Friend joins your reservation' WHERE `point_source_detail`.`id` =17;");
        $this->execute("UPDATE  `point_source_detail` SET `name` = 'Mark a restaurant as loved it' WHERE `point_source_detail`.`id` =16;");
        $this->execute("UPDATE  `point_source_detail` SET `name` = 'Add a food item to your crave list' WHERE `point_source_detail`.`id` =15;");
        $this->execute("UPDATE  `point_source_detail` SET `name` = 'Mark a food item as loved it' WHERE `point_source_detail`.`id` =14;");
        $this->execute("UPDATE  `point_source_detail` SET `name` = 'Register with a social account' WHERE `point_source_detail`.`id` =13;");
        $this->execute("UPDATE  `point_source_detail` SET `name` = 'Register via email' WHERE `point_source_detail`.`id` =12;");
        $this->execute("UPDATE  `point_source_detail` SET `name` = 'Email us about a major bug on MunchAdo.com' WHERE `point_source_detail`.`id` =8;");
        $this->execute("UPDATE  `point_source_detail` SET `name` = 'Upload pictures from your Munch Ado experiences' WHERE `point_source_detail`.`id` =7;");
        $this->execute("UPDATE  `point_source_detail` SET `name` = 'Review a restaurant' WHERE `point_source_detail`.`id` =6;");
        $this->execute("UPDATE  `point_source_detail` SET `name` = 'Friend accepts your invitation' WHERE `point_source_detail`.`id` =5;");
    }//up()

    public function down()
    {
    }//down()
}
