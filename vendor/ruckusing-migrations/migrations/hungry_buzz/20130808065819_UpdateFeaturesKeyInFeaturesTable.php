<?php

class UpdateFeaturesKeyInFeaturesTable extends Ruckusing_Migration_Base
{
    public function up()
    {
     $this->execute("UPDATE `hungry_buzz`.`features` SET `search_status` = '1' WHERE `features` = 'Good For Date'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Casual Dining' WHERE `features` = 'Casual'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Cafe' WHERE `features` = 'Cafe'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Fine Dining' WHERE `features` = 'Fine Dining'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Tea House' WHERE `features` = 'Tea House'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Pub/Bar' WHERE `features` = 'Pub'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Buffet' WHERE `features` = 'Buffet'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Wine and Beer' WHERE `features` = 'Beer & Wine'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'BYOB' WHERE `features` = 'BYOB'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Gaming' WHERE `features` = 'Gaming'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Happy Hour' WHERE `features` = 'Happy Hour'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Outdoor Seating' WHERE `features` = 'Outdoor Seating'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Parking' WHERE `features` = 'Parking'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Prix-Fixe' WHERE `features` = 'Prix-Fixe'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Accessible' WHERE `features` = 'Wheelchair Accessible'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Wifi' WHERE `features` = 'Wifi'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Raw Bar' WHERE `features` = 'Raw Bar'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Live Music' WHERE `features` = 'Live Entertainment'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Open 24 hours' WHERE `features` = 'Open 24 hours'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Business' WHERE `features` = 'Good for Business'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Group' WHERE `features` = 'Good for groups'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Kids' WHERE `features` = 'Good for Kids'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Single' WHERE `features` = 'Good for Singles'");
     $this->execute("UPDATE `hungry_buzz`.`features` SET `status` = '1',`features_key` = 'Date' WHERE `features` = 'Good For Date'");
 
    }//up()

    public function down()
    {
    }//down()
}
