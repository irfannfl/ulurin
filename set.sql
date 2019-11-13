CREATE TABLE `redirect` (
  `id` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(620) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `hits` bigint(20) NOT NULL DEFAULT '0',
  `user` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Used for the URL shortener';

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `redirect`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
