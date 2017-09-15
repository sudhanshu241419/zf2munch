<?php

class CreateTableCmsS3Upload extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("CREATE TABLE IF NOT EXISTS `cms_s3_upload` (
                        `id` bigint(20) NOT NULL AUTO_INCREMENT,
                        `rest_code` varchar(20) NOT NULL,
                        `image_count` int(11) DEFAULT NULL,
                        `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        `uploaded_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                        `upload_status` tinyint(4) DEFAULT '0' COMMENT '0=>not uploaded, 1=>uploaded',
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `res_code_i` (`rest_code`),
                        KEY `upload_status_i` (`upload_status`)
                      ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;");
    }

    public function down() {
        $this->query("DROP TABLE cms_s3_upload;");
    }
    
}
