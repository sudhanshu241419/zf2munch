<?php

class CreateTableDashboardAuth extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `dashboard_auth` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `dashboard_id` int(11) unsigned DEFAULT NULL,
  `dashboard_details` mediumtext COLLATE utf8_unicode_ci,
  `token` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `ttl` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `last_update_timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_UNIQUE` (`token`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1");
    }//up()

    public function down()
    {
    }//down()
}
