<?php

class PreOrderTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$pre_order_data = $this->create_table("pre_order_data");

    		$pre_order_data->column("restaurant_id", "integer");
    		$pre_order_data->column("user_id", "integer");
    		$pre_order_data->column("city_code", "string");
    		$pre_order_data->column("city", "string");
    		$pre_order_data->column("address", "string");
    		$pre_order_data->column("order_type", "string");
    		$pre_order_data->column("delivery_time", "datetime");
    		$pre_order_data->column("min_order_exceed", "integer");
    		$pre_order_data->column("sub_total", "integer");
    		$pre_order_data->column("delivery_charges", "integer");
    		$pre_order_data->column("tax", "integer");
    		$pre_order_data->column("tip", "integer");
    		$pre_order_data->finish();

    	$pre_order_item_data = $this->create_table("pre_order_item_data");

    		$pre_order_item_data->column("order_id", "integer");
    		$pre_order_item_data->column("item", "string");
    		$pre_order_item_data->column("quantity", "integer");
    		$pre_order_item_data->column("unit_price", "integer");
    		$pre_order_item_data->column("total_item_amt", "integer");
    		$pre_order_item_data->column("item_description", "string");
    		$pre_order_item_data->column("special_instruction", "string");
    		$pre_order_item_data->finish();
    }//up()

    public function down()
    {
    	 $this->drop_table("pre_order_data");
    	 $this->drop_table("pre_order_item_data");
    }//down()
}
