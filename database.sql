DROP TABLE IF EXISTS `_invitar_tree`;
CREATE TABLE `_invitar_tree` (
  `invitation_id` int(11) NOT NULL AUTO_INCREMENT,
  `invitation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `email_inviter` varchar(50) NOT NULL,
  `email_invited` varchar(50) NOT NULL,
  `used` tinyint(1) NOT NULL,
  `used_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `source` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`invitation_id`)
);