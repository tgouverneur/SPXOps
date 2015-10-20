
INSERT INTO `list_rjob` (`id`, `class`, `fct`, `arg`, `frequency`, `fk_login`, `t_last`, `t_add`, `t_upd`) VALUES (3,'Update','allServers','',14400,2,0,1347008471,1417291975),(5,'Check','serverChecks','',3600,2,0,1347008536,1417292049),(6,'Job','cleanJobs','',3600,2,0,1416611526,1417293006);

-- VM Support
INSERT INTO `list_rjob` (`class`, `fct`, `arg`, `frequency`, `fk_login`, `t_last`, `t_add`, `t_upd`) VALUES ('VM', 'detectHostnames', '', 7200, 2, 0, 0, 0);
INSERT INTO `list_rjob` (`class`, `fct`, `arg`, `frequency`, `fk_login`, `t_last`, `t_add`, `t_upd`) VALUES ('VM', 'detectOSes', '', 7200, 2, 0, 0, 0);
INSERT INTO `list_rjob` (`class`, `fct`, `arg`, `frequency`, `fk_login`, `t_last`, `t_add`, `t_upd`) VALUES ('Update', 'allVMs', '', 14400, 2, 0, 0, 0);
INSERT INTO `list_rjob` (`class`, `fct`, `arg`, `frequency`, `fk_login`, `t_last`, `t_add`, `t_upd`) VALUES ('Check', 'vmChecks', '', 3600, 2, 0, 0, 0);
