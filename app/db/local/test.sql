create database bsphp;

use bsphp;

CREATE TABLE IF NOT EXISTS `test` (
  `test_rowid` int(11) NOT NULL AUTO_INCREMENT COMMENT '/**/',
  `userid` varchar(32) NOT NULL COMMENT '/*trim|min_length[6]|xss_clean*/',
  `nick` varchar(16) NOT NULL COMMENT '/*trim|min_length[6]|xss_lean*/',
  PRIMARY KEY (`userid`),
  UNIQUE KEY `test_rowid` (`test_rowid`,`nick`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `test`(`userid`,`nick`)VALUES('projectBS','nickname');

