<?php

class AlterAvatarForTempMessage extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("ALTER TABLE `avatar` ADD `temp_message` TEXT NOT NULL AFTER `message` ");
        $this->execute("UPDATE `MunchAdo`.`avatar` SET `temp_message` ='Channel your inner Zen master, and order Asian food from five or more restaurants to add the Fu Munchu to your dining table.' WHERE`avatar`.`id` =1");
        $this->execute("UPDATE `MunchAdo`.`avatar` SET `temp_message` ='Claim your organic bragging rights by checking in, ordering from or reserving a table at five or more Gluten-Free, vegan/vegetarian, or health food restaurants.' WHERE`avatar`.`id` =2");
        $this->execute("UPDATE `MunchAdo`.`avatar` SET `temp_message` ='Refine your palate by ordering food or reserving a table at five or more restaurants with a burger on their menu.' WHERE`avatar`.`id` =3");
        $this->execute("UPDATE `MunchAdo`.`avatar` SET `temp_message` ='Fill in to a corner booth, and reserve a table at five restaurants to earn the V.I.P pass.' WHERE`avatar`.`id` =4");
        $this->execute("UPDATE `MunchAdo`.`avatar` SET `temp_message` ='Set the table with plastic utensils and order delivery from five or more restaurants and declare yourself a Stay At Home Eater.' WHERE`avatar`.`id` =5");
        $this->execute("UPDATE `MunchAdo`.`avatar` SET `temp_message` ='Set up five takeout orders from different restaurants and pick up a little something extra along the way.' WHERE`avatar`.`id` =6");
        $this->execute("UPDATE `MunchAdo`.`avatar` SET `temp_message` ='Share your thoughts and opinions by leaving five reviews and let the world know you''re a voice to be heard.' WHERE`avatar`.`id` =7");
        $this->execute("UPDATE `MunchAdo`.`avatar` SET `temp_message` ='Invite and have five of your friends join Munch Ado and you''ll truly be all that and a side of fries.' WHERE`avatar`.`id` =8");
        $this->execute("UPDATE `MunchAdo`.`avatar` SET `temp_message` ='Prove you''re a pizza connoisseur and order from five restaurants with pizza on the menu.' WHERE`avatar`.`id` =9");
    }//up()

    public function down()
    {
    }//down()
}
