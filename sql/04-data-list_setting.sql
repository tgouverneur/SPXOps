
INSERT INTO `list_setting` (`id`, `cat`, `name`, `textname`, `description`, `value`, `placeholder`, `t_add`, `t_upd`) VALUES (1,'user','minpassword','Minimum Password Length','0','5','',0,1416643450),(2,'general','sitename','Site Name','0','SPXOps','',-1,1416643832),(3,'display','serverPerPage','Number of Server per page','0','25','',-1,1416748495),(4,'display','pserverPerPage','Number of Physical Server per page','0','20','',-1,1416643450),(5,'display','actPerPage','Number of Activities per page','0','50','',-1,1416643450),(6,'display','loginPerPage','Number of Login per page','0','10','',-1,1416643450),(7,'display','clusterPerPage','Number of Cluster per page','0','10','',-1,1416643450),(8,'display','sgroupPerPage','Number of Server Group per page','0','25','',-1,1416748495),(9,'display','ugroupPerPage','Number of User Group per page','0','25','',-1,1416748495),(13,'display','dateFormat','Date format','','d-m-Y','',-1,1416643450),(14,'display','timeFormat','Time Format','','d-m-Y H:m:s','',-1,1416643450),(15,'daemon','sleepTime','Time to sleep between two run','','5','',-1,1416643450),(16,'daemon','nrProcess','Number of daemons','','20','',-1,1416665538),(17,'display','vmPerPage','Number of VMs per page','0','100','',-1,1416748495),(18,'display','jobPerPage','Number of Jobs per page','0','100','',-1,1416748495),(19,'general','mailfrom','Mail From', 'From: email used when sending e-mails out', 'spxops@localdomain.tld', '', -1, -1);


INSERT INTO `list_setting` (`cat`, `name`, `textname`, `description`, `value`, `placeholder`) VALUES ('general', 'allowRegistration', 'Allow users to register', '', '1', '0 or 1');


-- Settings for VMs
INSERT INTO `list_setting` (`cat`, `name`, `textname`, `description`, `value`, `placeholder`) VALUES ('vm', 'enable', 'Allow VMs Indexations', '', '0', '0 or 1');
INSERT INTO `list_setting` (`cat`, `name`, `textname`, `description`, `value`, `placeholder`) VALUES ('vm', 'detect_tries', 'How many times should we try', '', '3', '');
INSERT INTO `list_setting` (`cat`, `name`, `textname`, `description`, `value`, `placeholder`) VALUES ('vm', 'dns_search', 'DNS Search', 'List of domains separated by comma', '', '');

-- SSL enforce
INSERT INTO `list_setting` (`cat`, `name`, `textname`, `description`, `value`, `placeholder`) VALUES ('general', 'enforceSSL', 'Enforce HTTPS', 'Redirects HTTP to HTTPS', '0', '0=no, 1=yes');

-- OATH
INSERT INTO `list_setting` (`cat`, `name`, `textname`, `description`, `value`, `placeholder`) VALUES ('user', 'hotpMaxSkew', 'HOTP Counter Max Skew', '', '10', '');
INSERT INTO `list_setting` (`cat`, `name`, `textname`, `description`, `value`, `placeholder`) VALUES ('user', 'oathLockReplayAttack', 'OATH Lock Replay Attacks', '', '1', '0=no, 1=yes');

