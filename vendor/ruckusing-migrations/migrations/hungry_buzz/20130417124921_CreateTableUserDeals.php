<?php

class CreateTableUserDeals extends Ruckusing_Migration_Base
{
    public function up()
    {
    		$user_deals = $this->create_table("user_deals");

    		$user_deals->column("user_id", "integer");
    		$user_deals->column("restaurant_id", "integer");
    		$user_deals->column("title", "string");
    		$user_deals->column("price", "float");
    		$user_deals->column("order_id", "integer");
    		$user_deals->column("deal_id", "integer");
    		$user_deals->column("type", "string");
    		$user_deals->column("redeem_at", "datetime");
    		$user_deals->column("expiry_at", "datetime");
    		$user_deals->column("purchase_at", "datetime");
    		$user_deals->column("status", "string");
    		$user_deals->finish();
    }//up()
    public function down()
    {
    	$this->drop_table("user_deals");
    }//down()
}
