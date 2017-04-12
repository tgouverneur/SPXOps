--
-- Table structure for table `jt_check_sgroup`
--

CREATE TABLE `jt_check_sgroup` (
  `fk_check` int(11) NOT NULL,
  `fk_sgroup` int(11) NOT NULL,
  `f_except` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fk_check`,`fk_sgroup`)
) ENGINE=InnoDB;

--
-- Table structure for table `jt_clrg_server`
--

CREATE TABLE `jt_clrg_server` (
  `fk_clrg` int(11) NOT NULL,
  `fk_server` int(11) NOT NULL,
  `fk_zone` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`fk_clrg`,`fk_server`)
) ENGINE=InnoDB;

--
-- Table structure for table `jt_sgroup_ugroup`
--

CREATE TABLE `jt_sgroup_ugroup` (
  `fk_sgroup` int(11) NOT NULL,
  `fk_ugroup` int(11) NOT NULL,
  PRIMARY KEY (`fk_sgroup`,`fk_ugroup`)
) ENGINE=InnoDB;


--
-- Table structure for table `jt_alerttype_ugroup`
--

CREATE TABLE `jt_alerttype_ugroup` (
  `fk_alerttype` int(11) NOT NULL,
  `fk_ugroup` int(11) NOT NULL,
  PRIMARY KEY (`fk_alerttype`,`fk_ugroup`)
) ENGINE=InnoDB;

--
-- Table structure for table `jt_disk_pool`
--

CREATE TABLE `jt_disk_pool` (
  `fk_disk` int(11) NOT NULL,
  `fk_pool` int(11) NOT NULL,
  `slice` int(2) NOT NULL DEFAULT '0',
  `role` varchar(50) NOT NULL,
  PRIMARY KEY (`fk_disk`,`fk_pool`)
) ENGINE=InnoDB;

--
-- Table structure for table `jt_login_ugroup`
--

CREATE TABLE `jt_login_ugroup` (
  `fk_login` int(11) NOT NULL,
  `fk_ugroup` int(11) NOT NULL,
  PRIMARY KEY (`fk_login`,`fk_ugroup`)
) ENGINE=InnoDB;

--
-- Table structure for table `jt_right_ugroup`
--

CREATE TABLE `jt_right_ugroup` (
  `fk_right` int(11) NOT NULL,
  `fk_ugroup` int(11) NOT NULL,
  `level` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`fk_right`,`fk_ugroup`)
) ENGINE=InnoDB;

--
-- Table structure for table `jt_server_sgroup`
--

CREATE TABLE `jt_server_sgroup` (
  `fk_server` int(11) NOT NULL,
  `fk_sgroup` int(11) NOT NULL,
  PRIMARY KEY (`fk_server`,`fk_sgroup`)
) ENGINE=InnoDB;

--
-- Table structure for table `jt_vm_sgroup`
--

CREATE TABLE `jt_vm_sgroup` (
  `fk_vm` int(11) NOT NULL,
  `fk_sgroup` int(11) NOT NULL,
  PRIMARY KEY (`fk_vm`,`fk_sgroup`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_act`
--

CREATE TABLE `list_act` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `msg` varchar(255) NOT NULL,
  `fk_login` int(11) NOT NULL,
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_check`
--

CREATE TABLE `list_check` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(200) NOT NULL,
  `frequency` int(11) NOT NULL,
  `lua` longtext NOT NULL,
  `m_error` varchar(200) NOT NULL,
  `m_warn` varchar(200) NOT NULL,
  `f_noalerts` int(1) NOT NULL DEFAULT '0',
  `f_text` int(1) NOT NULL DEFAULT '0',
  `f_root` int(1) NOT NULL DEFAULT '0',
  `f_vm` int(1) NOT NULL DEFAULT '1',
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_clrg`
--

CREATE TABLE `list_clrg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(200) NOT NULL,
  `state` varchar(50) NOT NULL,
  `f_suspend` int(1) NOT NULL DEFAULT '0',
  `fk_cluster` int(11) NOT NULL,
  `t_add` int(11) NOT NULL DEFAULT '-1',
  `t_upd` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_clrs`
--

CREATE TABLE `list_clrs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(200) NOT NULL,
  `state` varchar(50) NOT NULL,
  `type` varchar(100) NOT NULL,
  `type_version` int(11) NOT NULL,
  `f_disabled` int(1) NOT NULL DEFAULT '0',
  `fk_clrg` int(11) NOT NULL,
  `t_add` int(11) NOT NULL DEFAULT '-1',
  `t_upd` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_cluster`
--

CREATE TABLE `list_cluster` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `fk_clver` int(11) NOT NULL DEFAULT '-1',
  `f_upd` int(1) NOT NULL DEFAULT '1',
  `t_upd` int(11) NOT NULL,
  `t_add` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_clver`
--

CREATE TABLE `list_clver` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `class` varchar(50) NOT NULL,
  `version` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_dataset`
--

CREATE TABLE `list_dataset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `size` bigint(22) NOT NULL,
  `available` bigint(22) NOT NULL,
  `compressratio` float NOT NULL DEFAULT '1.00',
  `creation` int(11) NOT NULL DEFAULT '-1',
  `reserved` bigint(22) NOT NULL,
  `used` bigint(22) NOT NULL,
  `uchild` bigint(22) NOT NULL,
  `type` varchar(20) DEFAULT NULL,
  `origin` varchar(200) DEFAULT NULL,
  `fk_pool` int(11) NOT NULL,
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_alerttype`
--

CREATE TABLE `list_alerttype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `short` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `desc` varchar(50) NOT NULL,
  `t_add` int(11) NOT NULL DEFAULT '-1',
  `t_upd` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;


--
-- Table structure for table `list_disk`
--

CREATE TABLE `list_disk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dev` varchar(200) NOT NULL,
  `vdev` varchar(200) NOT NULL,
  `drv` varchar(50) NOT NULL,
  `serial` varchar(100) NOT NULL,
  `class` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL,
  `vendor` varchar(100) NOT NULL,
  `product` varchar(100) NOT NULL,
  `rev` varchar(20) NOT NULL,
  `size` bigint(22) NOT NULL,
  `lunid` varchar(20) NOT NULL,
  `f_local` int(1) NOT NULL DEFAULT '1',
  `f_san` int(1) NOT NULL DEFAULT '0',
  `fk_server` int(11) NOT NULL,
  `fk_vm` int(11) NOT NULL,
  `t_add` int(11) NOT NULL DEFAULT '-1',
  `t_upd` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_hba`
--

CREATE TABLE `list_hba` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wwn` varchar(50) NOT NULL,
  `vendor` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  `firmware` varchar(100) NOT NULL,
  `fcode` varchar(100) NOT NULL,
  `serial` varchar(100) NOT NULL,
  `drv` varchar(50) NOT NULL,
  `drv_ver` varchar(50) NOT NULL,
  `state` varchar(20) NOT NULL,
  `osdev` varchar(10) NOT NULL,
  `curspeed` varchar(50) NOT NULL,
  `fk_server` int(11) NOT NULL,
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_job`
--

CREATE TABLE `list_job` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class` varchar(100) NOT NULL,
  `fct` varchar(100) NOT NULL,
  `pid` int(10) NOT NULL,
  `arg` text NOT NULL,
  `state` int(11) NOT NULL,
  `pc_progress` int(3) NOT NULL,
  `fk_login` int(11) NOT NULL,
  `fk_log` int(11) NOT NULL,
  `fk_pid` int(11) NOT NULL,
  `t_start` int(11) NOT NULL,
  `t_stop` int(11) NOT NULL,
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_joblog`
--

CREATE TABLE `list_joblog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rc` int(3) NOT NULL,
  `log` longtext NOT NULL,
  `fk_job` int(11) NOT NULL,
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_mail`
--

CREATE TABLE `list_mail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `msg` longtext NOT NULL,
  `headers` text NOT NULL,
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;


--
-- Table structure for table `list_lock`
--

CREATE TABLE `list_lock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_check` int(11) NOT NULL DEFAULT '-1',
  `fk_server` int(11) NOT NULL DEFAULT '-1',
  `fk_vm` int(11) NOT NULL DEFAULT '-1',
  `fk_pid` int(11) NOT NULL,
  `fct` varchar(15) NOT NULL,
  `t_add` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_log`
--

CREATE TABLE `list_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `msg` TEXT NOT NULL,
  `o_class` varchar(20) NOT NULL,
  `fk_what` int(11) NOT NULL,
  `fk_login` int(11) NOT NULL,
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_utoken`
--

CREATE TABLE `list_utoken` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `counter` int(11) NOT NULL DEFAULT '0',
  `secret` varchar(120) NOT NULL,
  `type` varchar(10) NOT NULL,
  `digit` int(1) NOT NULL DEFAULT '6',
  `f_init` int(1) NOT NULL DEFAULT '0',
  `f_locked` int(1) NOT NULL DEFAULT '0',
  `t_add` int(11) NOT NULL DEFAULT '-1',
  `t_upd` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_login`
--

CREATE TABLE `list_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(200) NOT NULL,
  `fk_utoken` int(11) NOT NULL DEFAULT '-1',
  `f_noalerts` int(1) NOT NULL DEFAULT '0',
  `f_active` int(1) NOT NULL DEFAULT '1',
  `f_admin` int(1) NOT NULL DEFAULT '0',
  `f_api` int(1) NOT NULL DEFAULT '0',
  `t_last` int(11) NOT NULL DEFAULT '0',
  `t_reset` int(11) NOT NULL DEFAULT '-1',
  `t_add` int(11) NOT NULL DEFAULT '-1',
  `t_upd` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_model`
--

CREATE TABLE `list_model` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `vendor` varchar(100) NOT NULL,
  `t_add` int(11) NOT NULL DEFAULT '-1',
  `t_upd` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_net`
--

CREATE TABLE `list_net` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ifname` varchar(20) NOT NULL,
  `layer` int(1) NOT NULL DEFAULT '3',
  `alias` varchar(50) NOT NULL,
  `version` int(1) NOT NULL DEFAULT '4',
  `address` varchar(50) NOT NULL,
  `netmask` varchar(50) NOT NULL,
  `group` varchar(50) NOT NULL,
  `flags` varchar(255) NOT NULL,
  `f_ipmp` int(1) NOT NULL DEFAULT '0',
  `fk_server` int(11) NOT NULL,
  `fk_vm` int(11) NOT NULL DEFAULT '-1',
  `fk_zone` int(11) NOT NULL,
  `fk_switch` int(11) NOT NULL DEFAULT '0',
  `fk_net` int(11) NOT NULL DEFAULT '0',
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_nfs`
--

CREATE TABLE `list_nfs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('share','mount') NOT NULL,
  `path` varchar(255) NOT NULL,
  `dest` varchar(255) NOT NULL,
  `share` varchar(255) NOT NULL,
  `acl` text NOT NULL,
  `size` bigint(22) NOT NULL,
  `used` bigint(22) NOT NULL,
  `fk_server` int(11) NOT NULL,
  `fk_vm` int(11) NOT NULL DEFAULT '-1',
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_os`
--

CREATE TABLE `list_os` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `uname` varchar(200) NOT NULL,
  `class` varchar(200) NOT NULL,
  `f_zone` int(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_patch`
--

CREATE TABLE `list_patch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_server` int(11) NOT NULL,
  `patch` varchar(15) NOT NULL,
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_pid`
--

CREATE TABLE `list_pid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent` varchar(20) NOT NULL,
  `pid` int(11) NOT NULL,
  `ppid` int(11) NOT NULL,
  `f_master` int(1) NOT NULL,
  `f_kill` int(1) NOT NULL DEFAULT '0',
  `t_upd` int(11) NOT NULL,
  `t_add` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agent` (`agent`,`pid`,`ppid`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_process`
--

CREATE TABLE `list_process` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `ppid` int(11) NOT NULL,
  `user` varchar(80) NOT NULL,
  `etime` varchar(100) NOT NULL,
  `ctime` varchar(100) NOT NULL,
  `tty` varchar(50) NOT NULL,
  `cmd` varchar(255) NOT NULL,
  `fk_server` int(11) NOT NULL DEFAULT '-1',
  `fk_vm` int(11) NOT NULL DEFAULT '-1',
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_pkg`
--

CREATE TABLE `list_pkg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `arch` varchar(20) NOT NULL,
  `version` varchar(200) NOT NULL,
  `basedir` varchar(200) NOT NULL,
  `vendor` varchar(200) NOT NULL,
  `desc` varchar(255) NOT NULL,
  `fmri` varchar(255) NOT NULL,
  `status` varchar(200) NOT NULL,
  `fk_server` int(11) NOT NULL,
  `fk_vm` int(11) NOT NULL,
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_pool`
--

CREATE TABLE `list_pool` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `type` varchar(50) NOT NULL,
  `size` bigint(22) NOT NULL,
  `used` bigint(22) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `f_cluster` int(1) NOT NULL DEFAULT '0',
  `fk_server` int(11) NOT NULL,
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_prj`
--

CREATE TABLE `list_prj` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `prjid` int(10) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `ulist` varchar(255) NOT NULL,
  `glist` varchar(255) NOT NULL,
  `attrs` varchar(255) NOT NULL,
  `fk_server` int(11) NOT NULL,
  `t_add` int(11) NOT NULL DEFAULT '-1',
  `t_upd` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_pserver`
--

CREATE TABLE `list_pserver` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `serial` varchar(200) NOT NULL,
  `fk_model` int(11) NOT NULL DEFAULT '-1',
  `t_add` int(11) NOT NULL DEFAULT '-1',
  `t_upd` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_result`
--

CREATE TABLE `list_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rc` int(2) NOT NULL DEFAULT '0',
  `message` varchar(200) NOT NULL,
  `details` text NOT NULL,
  `f_ack` int(1) NOT NULL DEFAULT '0',
  `fk_check` int(11) NOT NULL DEFAULT '-1',
  `fk_server` int(11) NOT NULL DEFAULT '-1',
  `fk_vm` int(11) NOT NULL DEFAULT '-1',
  `fk_login` int(11) NOT NULL DEFAULT '-1',
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_right`
--

CREATE TABLE `list_right` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `short` varchar(20) NOT NULL,
  `t_add` int(11) NOT NULL DEFAULT '-1',
  `t_upd` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_rjob`
--

CREATE TABLE `list_rjob` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class` varchar(100) NOT NULL,
  `fct` varchar(100) NOT NULL,
  `arg` text NOT NULL,
  `frequency` int(11) NOT NULL,
  `fk_login` int(11) NOT NULL,
  `t_last` int(11) NOT NULL,
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_server`
--

CREATE TABLE `list_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostname` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `fk_pserver` int(11) NOT NULL DEFAULT '-1',
  `fk_os` int(11) NOT NULL DEFAULT '-1',
  `fk_suser` int(11) NOT NULL DEFAULT '-1',
  `fk_cluster` int(11) NOT NULL DEFAULT '-1',
  `f_rce` int(1) NOT NULL DEFAULT '0',
  `f_upd` int(1) NOT NULL DEFAULT '0',
  `t_add` int(11) NOT NULL DEFAULT '-1',
  `t_upd` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_setting`
--

CREATE TABLE `list_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `textname` varchar(100) NOT NULL,
  `description` varchar(200) NOT NULL,
  `value` varchar(1000) NOT NULL,
  `placeholder` varchar(100) NOT NULL,
  `t_add` int(11) NOT NULL DEFAULT '-1',
  `t_upd` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_sgroup`
--

CREATE TABLE `list_sgroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(200) NOT NULL,
  `t_add` int(11) NOT NULL DEFAULT '-1',
  `t_upd` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_suser`
--

CREATE TABLE `list_suser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(200) NOT NULL,
  `description` varchar(200) NOT NULL,
  `pubkey` varchar(255) NOT NULL,
  `t_add` int(11) NOT NULL DEFAULT '-1',
  `t_upd` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_switch`
--

CREATE TABLE `list_switch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `did` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `sfver` text NOT NULL,
  `platform` varchar(200) NOT NULL,
  `location` varchar(255) NOT NULL,
  `oid` varchar(200) NOT NULL,
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_ugroup`
--

CREATE TABLE `list_ugroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(200) NOT NULL,
  `t_add` int(11) NOT NULL DEFAULT '-1',
  `t_upd` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_rrd`
--

CREATE TABLE `list_rrd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(500) NOT NULL,
  `type` varchar(50) NOT NULL,
  `name` varchar(500) NOT NULL,
  `f_lock` int(1) NOT NULL,
  `fk_server` int(11) NOT NULL,
  `fk_pool` int(11) NOT NULL,
  `fk_disk` int(11) NOT NULL,
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_slr`
--

CREATE TABLE `list_slr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `definition` text NOT NULL,
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_vm`
--

CREATE TABLE `list_vm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `hostname` varchar(200) NOT NULL,
  `status` varchar(20) NOT NULL,
  `xml` longtext NOT NULL,
  `livexml` longtext NOT NULL,
  `fk_server` int(11) NOT NULL,
  `fk_os` int(11) NOT NULL DEFAULT '-1',
  `fk_suser` int(11) NOT NULL DEFAULT '-1',
  `f_upd` int(1) NOT NULL DEFAULT '0',
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `list_zone`
--

CREATE TABLE `list_zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL,
  `path` varchar(255) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `iptype` varchar(50) NOT NULL,
  `zoneid` int(11) NOT NULL,
  `hostname` varchar(200) NOT NULL,
  `fk_server` int(11) NOT NULL,
  `t_add` int(11) NOT NULL,
  `t_upd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

--
-- Table structure for table `nfo_login`
--

CREATE TABLE `nfo_login` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` text NOT NULL,
  `u` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`,`name`)
) ENGINE=InnoDB;


--
-- Table structure for table `nfo_cluster`
--

CREATE TABLE `nfo_cluster` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` text NOT NULL,
  `u` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`,`name`)
) ENGINE=InnoDB;

--
-- Table structure for table `nfo_server`
--

CREATE TABLE `nfo_server` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` text NOT NULL,
  `u` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`,`name`)
) ENGINE=InnoDB;

--
-- Table structure for table `nfo_dataset`
--

CREATE TABLE `nfo_dataset` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` text NOT NULL,
  `u` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`,`name`)
) ENGINE=InnoDB;

--
-- Table structure for table `nfo_vm`
--

CREATE TABLE `nfo_vm` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` text NOT NULL,
  `u` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`,`name`)
) ENGINE=InnoDB;

--
-- Table structure for table `nfo_zone`
--

CREATE TABLE `nfo_zone` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` text NOT NULL,
  `u` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`,`name`)
) ENGINE=InnoDB;


