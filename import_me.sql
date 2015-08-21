CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(22) COLLATE latin1_general_ci NOT NULL,
  `key` varchar(22) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(22) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `users` (
  `id` int(22) NOT NULL AUTO_INCREMENT,
  `user` varchar(22) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `key` varchar(22) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `enabled` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;
