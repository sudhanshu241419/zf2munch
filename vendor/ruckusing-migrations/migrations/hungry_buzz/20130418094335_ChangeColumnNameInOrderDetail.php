<?php

class ChangeColumnNameInOrderDetail extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("alter table user_order_details drop FOREIGN KEY FK_user_order_details");
    	$this->rename_column('user_order_details', 'order_id', 'user_order_id');
    	$this->execute("alter table user_order_details add foreign key(user_order_id) references user_orders(id)
			on delete cascade");

    }//up()

    public function down()
    {
    	$this->execute("alter table user_order_details drop FOREIGN KEY user_order_details_ibfk_1");
    	$this->rename_column('user_order_details', 'user_order_id', 'order_id');
    	$this->execute("alter table user_order_details add foreign key(order_id) references user_orders(id)
			on delete cascade");
    }//down()
}
