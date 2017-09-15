<?php

class InsertContentInReplicaRestaurantDealsCoupons extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("INSERT INTO `replica_restaurant_deals_coupons` (`id`, `restaurant_id`, `city_id`, `type`, `title`, `description`, `fine_print`, `price`, `discount_type`, `discount`, `start_on`, `end_date`, `expired_on`, `created_on`, `updated_at`, `image`, `status`, `trend`, `sold`, `redeemed`) VALUES
		(1, 2, 22, 'deals', '$11.95 for a choice of kabob combos', 'a choice of kabob combos $11.95.', NULL, 100.00, 'p', 10, '2013-08-13 08:00:00', '2013-08-31 10:00:00', NULL, NULL, NULL, 'buffalo-chicken-wrap.jpg', 1, NULL, 2, 2),
		(2, 2, 22, 'coupons', '$5 Off Dinner Above $20', '$5 Off Dinner Above ', NULL, 200.00, 'f', 20, '2013-08-23 06:00:00', '2013-08-24 08:00:00', NULL, NULL, NULL, 'buffalo-chicken-wrap.jpg', 1, NULL, 5, 4),
		(3, 2, 22, 'deals', '$15.95 for a choice of kabob combos', '$15.95 for a choice of kabob combos', NULL, 500.00, 'p', 50, '2013-08-21 07:00:00', '2013-08-30 12:00:00', NULL, NULL, NULL, 'chicken-cutlet.jpg', 1, NULL, 5, 5),
		(4, 2, 22, 'coupons', '$5 Off Dinner Above $20', '$5 Off Dinner Above $20', NULL, 200.00, 'p', 20, '2013-08-23 09:13:00', '2013-08-30 06:27:00', NULL, NULL, NULL, 'garlic-bread.jpg', 1, NULL, 2, 2),
		(5, 2, 22, 'deals', '$50 for a choice of kabob combos', '$50 for a choice of kabob combos', NULL, 400.00, 'f', 50, '2013-08-14 07:00:00', '2013-08-30 11:00:00', NULL, NULL, NULL, 'garlic-bread.jpg', 1, NULL, 2, 2),
		(6, 2, 22, 'coupons', '$5 Off Dinner Above $20', '$5 Off Dinner Above $20', NULL, 500.00, 'p', 10, '2013-08-28 11:00:00', '2013-08-31 11:00:00', NULL, NULL, NULL, 'garlic-bread.jpg', 1, NULL, 5, 5),
		(7, 2, 22, 'deals', '$11.95 for a choice of kabob combos', '$11.95 for a choice of kabob combos', NULL, 500.00, 'p', 5, '2013-08-16 11:00:00', '2013-08-31 00:57:00', NULL, NULL, NULL, 'garlic-bread.jpg', 1, NULL, 3, 4),
		(8, 2, 22, 'coupons', '$5 Off Dinner Above $20', '$5 Off Dinner Above $20', NULL, 300.00, 'p', 10, '2013-08-23 15:00:00', '2013-08-22 00:42:00', NULL, NULL, NULL, 'chicken-cutlet.jpg', 1, NULL, 2, 2),
		(9, 2, 22, 'deals', '$11.95 for a choice of kabob combos', '$11.95 for a choice of kabob combos', NULL, 400.00, 'p', 20, '2013-08-16 00:38:00', '2013-08-30 00:59:00', NULL, NULL, NULL, 'chicken-cutlet.jpg', 1, NULL, 1, 1),
		(10, 2, 22, 'coupons', '$5 Off Dinner Above $20', '$5 Off Dinner Above $20', NULL, 600.00, 'p', 20, '2013-08-21 00:00:59', '2013-08-28 00:18:00', NULL, NULL, NULL, 'garlic-bread.jpg', 1, NULL, 2, 2),
		(11, 2, 22, 'deals', '$11.95 for a choice of kabob combos', '$11.95 for a choice of kabob combos', NULL, 500.00, 'p', 25, '2013-08-22 00:42:00', '2013-08-30 15:00:00', NULL, NULL, NULL, 'chicken-cutlet.jpg', 1, NULL, 3, 3),
		(12, 2, 22, 'coupons', '$5 Off Dinner Above $20', '$5 Off Dinner Above $20', NULL, 400.00, 'f', 50, '2013-08-23 21:00:00', '2013-08-30 00:32:00', NULL, NULL, NULL, 'garlic-bread.jpg', 1, NULL, 2, 2);");
		    }//up()

    public function down()
    {
    }//down()
}
