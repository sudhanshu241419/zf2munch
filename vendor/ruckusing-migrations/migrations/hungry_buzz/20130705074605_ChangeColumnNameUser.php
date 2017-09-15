<?php

class ChangeColumnNameUser extends Ruckusing_Migration_Base{
  public function up(){
    $this->add_column("user_addresses", "apt_suite", "text");
    $this->execute("ALTER TABLE `user_addresses` CHANGE `apt_suite` `apt_suite` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL");
    $this->execute("ALTER TABLE `user_addresses` CHANGE `delivery_instructions` `delivery_instructions` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL");
    $this->execute("ALTER TABLE `user_addresses` CHANGE `takeout_instructions` `takeout_instructions` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL");
    $this->rename_column("users","delevary_instructions", "delivery_instructions");
  }//up()

  public function down(){
    $this->execute("ALTER TABLE `user_addresses` CHANGE `apt_suite` `apt_suite` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL");
    $this->execute("ALTER TABLE `user_addresses` CHANGE `delivery_instructions` `delivery_instructions` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL");
    $this->execute("ALTER TABLE `user_addresses` CHANGE `takeout_instructions` `takeout_instructions` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL");
    $this->remove_column("user_addresses", "apt_suite");
    $this->rename_column("users","delivery_instructions", "delevary_instructions");
    $this->remove_column("user_addresses", "apt_suite");
    $this->execute("ALTER TABLE `user_addresses` CHANGE `apt_suite` `apt_suite` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL ,
                    CHANGE `delivery_instructions` `delivery_instructions` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL ,
                   CHANGE `takeout_instructions` `takeout_instructions` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL");
    
    $this->rename_column("users","delivery_instructions", "delevary_instructions");
  }//down()
}
?>
