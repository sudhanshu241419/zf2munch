<?php

class CreateTableLogError extends Ruckusing_Migration_Base {

    public function up() {
        $this->execute("CREATE TABLE IF NOT EXISTS log_error (
                        id bigint(20) NOT NULL AUTO_INCREMENT,
                        level tinyint(4) NOT NULL COMMENT '1=CRITICAL,2=ERROR,3=WARNING,4=INFO',
                        message varchar(255) NOT NULL,
                        origin varchar(255) NOT NULL COMMENT 'has file and line info',
                        happened_at datetime NOT NULL,
                        PRIMARY KEY (id)
                      ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1");
    }

    public function down() {
        $this->execute("DROP TABLE log_error");
    }
}
