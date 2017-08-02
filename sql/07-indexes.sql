CREATE INDEX list_dataset_name ON list_dataset (name);
CREATE INDEX list_dataset_fk_pool ON list_dataset (fk_pool);
CREATE INDEX list_dataset_pool_name ON list_dataset (fk_pool,name);
CREATE INDEX list_pkg_name_serv ON list_pkg (name, fk_server);
CREATE INDEX list_pkg_fk_vm ON list_pkg(fk_vm);
CREATE INDEX list_server_hostname ON list_server (hostname);
CREATE INDEX list_vm_name ON list_vm (name);
CREATE INDEX list_patch_name_serv ON list_patch (patch,fk_server);
CREATE INDEX list_disk_dev_serv ON list_disk (dev,fk_server);
CREATE INDEX list_nfs_type_srv_share ON list_nfs (type,fk_server,share);
CREATE INDEX list_login_username ON list_login (username);
CREATE INDEX list_pserver_name ON list_pserver (name);
CREATE INDEX nfo_cl_id_name ON nfo_cluster (id,name);
CREATE INDEX nfo_lo_id_name ON nfo_login (id,name);
CREATE INDEX nfo_srv_id_name ON nfo_server (id,name);
CREATE INDEX nfo_vm_id_name ON nfo_vm (id,name);
CREATE INDEX nfo_zone_id_name ON nfo_zone (id,name);
CREATE INDEX list_job_state_fk_pid ON list_job(state, fk_pid);
CREATE INDEX list_job_state_fk_pid_t_add ON list_job(state, fk_pid, t_add);
CREATE INDEX list_disk_fk_vm ON list_disk(fk_vm);
CREATE INDEX list_nfs_fk_vm_type ON list_nfs(fk_vm, type);
CREATE INDEX list_pid_pid_agent ON list_pid(pid, agent);
CREATE INDEX jt_disk_pool_fk_pool ON jt_disk_pool(fk_pool);
CREATE INDEX list_log_o_class_fk_what ON list_log(o_class,fk_what);
