<?php

class RenameTableReplicaRestaurantDealsCoupons extends Ruckusing_Migration_Base
{
    public function up()
    {

    	$this->execute("RENAME TABLE  `hungry_buzz`.`replica_restaurant_deals_coupons` TO  `hungry_buzz`.`restaurant_deals_coupons_sample`");
    	$this->execute("TRUNCATE TABLE  `hungry_buzz`.`restaurant_deals_coupons_sample`");
    	$this->execute("INSERT INTO `restaurant_deals_coupons_sample` (`id`, `restaurant_id`, `city_id`, `type`, `title`, `description`, `fine_print`, `price`, `discount_type`, `discount`, `start_on`, `end_date`, `expired_on`, `created_on`, `updated_at`, `image`, `status`, `trend`, `sold`, `redeemed`) VALUES
			(1, 2, 22, 'deals', '$11.95 for a choice of kabob combos', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellen tesque sem nunc, lacinia ut tempus sit.', NULL, 100.00, 'f', 10, '2013-08-13 08:00:00', '2013-08-31 10:00:00', '2013-08-31 00:00:00', '2013-08-14 00:00:00', '2013-08-21 00:00:00', 'buffalo-chicken-wrap.jpg', 1, NULL, 2, 2),
			(2, 2, 22, 'coupons', '$5 Off Dinner Above $20', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellen tesque sem nunc, lacinia ut tempus sit.', NULL, 200.00, 'f', 20, '2013-08-23 06:00:00', '2013-08-24 08:00:00', '2013-08-31 00:00:00', '2013-08-06 00:00:00', '2013-08-13 00:00:00', 'buffalo-chicken-wrap.jpg', 1, NULL, 5, 4),
			(3, 2, 22, 'deals', '$15.95 for a choice of kabob combos', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellen tesque sem nunc, lacinia ut tempus sit.', NULL, 500.00, 'p', 50, '2013-08-21 07:00:00', '2013-08-30 12:00:00', '2013-08-29 00:00:00', '2013-08-20 00:00:00', '2013-08-22 00:00:00', 'chicken-cutlet.jpg', 1, NULL, 5, 5),
			(4, 2, 22, 'coupons', '$5 Off Dinner Above $20', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellen tesque sem nunc, lacinia ut tempus sit.', NULL, 200.00, 'p', 20, '2013-08-23 09:13:00', '2013-08-30 06:27:00', '2013-08-31 00:00:00', '2013-08-14 00:00:00', '2013-08-24 00:00:00', 'garlic-bread.jpg', 1, NULL, 2, 2),
			(5, 2, 22, 'deals', '$50 for a choice of kabob combos', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellen tesque sem nunc, lacinia ut tempus sit.', NULL, 400.00, 'f', 50, '2013-08-14 07:00:00', '2013-08-30 11:00:00', '2013-08-30 00:00:00', '2013-08-06 00:00:00', '2013-08-21 00:00:00', 'garlic-bread.jpg', 1, NULL, 2, 2),
			(6, 2, 22, 'coupons', '$5 Off Dinner Above $20', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellen tesque sem nunc, lacinia ut tempus sit.', NULL, 500.00, 'p', 10, '2013-08-28 11:00:00', '2013-08-31 11:00:00', '2013-08-31 00:00:00', '2013-08-01 00:00:00', '2013-08-20 00:00:00', 'garlic-bread.jpg', 1, NULL, 5, 5),
			(7, 2, 22, 'deals', '', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellen tesque sem nunc, lacinia ut tempus sit.', NULL, 500.00, 'p', 5, '2013-08-16 11:00:00', '2013-08-31 00:57:00', NULL, NULL, NULL, 'garlic-bread.jpg', 1, NULL, 3, 4),
			(8, 2, 22, 'coupons', '$5 Off Dinner Above $20', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellen tesque sem nunc, lacinia ut tempus sit.', NULL, 300.00, 'p', 10, '2013-08-23 15:00:00', '2013-08-22 00:42:00', NULL, NULL, NULL, 'chicken-cutlet.jpg', 1, NULL, 2, 2),
			(9, 2, 22, 'deals', '$11.95 for a choice of kabob combos', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellen tesque sem nunc, lacinia ut tempus sit.', NULL, 400.00, 'p', 20, '2013-08-16 00:38:00', '2013-08-30 00:59:00', NULL, NULL, NULL, 'chicken-cutlet.jpg', 1, NULL, 1, 1),
			(10, 2, 22, 'coupons', '$5 Off Dinner Above $20', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellen tesque sem nunc, lacinia ut tempus sit.', NULL, 600.00, 'p', 20, '2013-08-21 00:00:59', '2013-08-28 00:18:00', NULL, NULL, NULL, 'garlic-bread.jpg', 1, NULL, 2, 2),
			(11, 2, 22, 'deals', '$11.95 for a choice of kabob combos', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellen tesque sem nunc, lacinia ut tempus sit.', NULL, 500.00, 'p', 25, '2013-08-22 00:42:00', '2013-08-30 15:00:00', NULL, NULL, NULL, 'chicken-cutlet.jpg', 1, NULL, 3, 3),

			(12, 2, 22, 'coupons', '$5 Off Dinner Above $20', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellen tesque sem nunc, lacinia ut tempus sit.', NULL, 400.00, 'f', 50, '2013-08-23 21:00:00', '2013-08-30 00:32:00', NULL, NULL, NULL, 'garlic-bread.jpg', 1, NULL, 2, 2)");

    }


    public function down()
    {
    }//down()
}
