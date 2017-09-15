<?php
namespace Restaurant\Model;

use MCommons\Model\AbstractModel;

class PreOrderItem extends AbstractModel
{

    public $id;

    public $pre_order_id;

    public $user_id;

    public $item;

    public $quantity;

    public $unit_price;

    public $total_item_amt;

    public $item_description;

    public $special_instruction;

    public $order_token;

    public $pre_order_addon_data;

    public $status;

    public $item_id;

    public $item_price_id;

    protected $_primary_key = 'id';

    const ORDER_PENDING = '0';

    const ORDER_CHECKOUT = '1';

    protected $_db_table_name = 'Restaurant\Model\DbTable\PreOrderItemTable';

    public function addtoPreOrderItem()
    {
        $data = $this->toArray();
        
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $rowsAffected = $writeGateway->insert($data);
        
        // Get the last insert id and update the model accordingly
        $lastInsertId = $writeGateway->getAdapter()
            ->getDriver()
            ->getLastGeneratedValue();
        
        if ($rowsAffected >= 1) {
            
            return $lastInsertId;
        }
        return false;
    }

    public function getPreOrder(array $options = array())
    {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        return $this->find($options)->toArray();
    }

    public function getPreOrderItem($orderDetails)
    {
        if (empty($orderDetails)) {
            $orderDescription = "No Item";
        } else {
            $orderDescription = '';
            foreach ($orderDetails as $key => $details) {
                $orderDescription .= $details['quantity'] . ' ' . $details['item'].',';
            }
            if(!empty($orderDescription)){
            	$orderDescription = substr($orderDescription, 0,-1);
            }
        }
        
        return empty($orderDescription) ? '' : $orderDescription;
    }
}
