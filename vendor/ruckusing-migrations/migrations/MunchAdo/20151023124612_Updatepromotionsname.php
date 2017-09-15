<?php

class Updatepromotionsname extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("UPDATE  `promotions` SET  `promotionName` =  'Opening Night' WHERE `promotionId` =1");
        $this->execute("UPDATE  `promotions` SET  `promotionName` =  '$15 Cash Back' WHERE `promotionId` =2");
        $this->execute("UPDATE  `promotions` SET  `promotionName` =  'Hispanic Night' WHERE `promotionId` =3");
    }//up()

    public function down()
    {
    }//down()
}
