-- **
-- * You might change the password below
-- *
-- * Thomas Gouverneur <thomas@espix.net>
-- **
CREATE USER 'spxops'@'localhost' IDENTIFIED BY 'Pohph9bieKu6Musee9ceeruhae3peiho';
GRANT ALL PRIVILEGES ON spxops.* TO 'spxops'@'localhost';
FLUSH PRIVILEGES;
-- EOF
