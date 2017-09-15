<?php

class Createnewtablenotificationtypes extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$notification_type = $this->create_table('notification_types');
    	$notification_type->column("user_id", "integer");
    	$notification_type->column("order_confirmation", "boolean");
    	$notification_type->column("order_delivered", "boolean");
    	$notification_type->column("reservation_confirmation", "boolean");
    	$notification_type->column("deal_coupon_purchased", "boolean");
    	$notification_type->column("monthly_points_summary", "boolean");
    	$notification_type->column("comments_on_reviews", "boolean");
    	$notification_type->column("system_updates", "boolean");
    	$notification_type->column("friend_acceptance_on_group_orders", "boolean");
    	$notification_type->finish();

    }//up()

    public function down()
    {
    	$this->drop_table("notification_types");
    }//down()
}
