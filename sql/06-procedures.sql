DELIMITER //
CREATE PROCEDURE deleteServer
(idServer INT)
BEGIN
    START TRANSACTION;
    DELETE FROM nfo_server WHERE id=idServer;
    DELETE FROM nfo_zone WHERE id IN (SELECT id FROM a_zone WHERE fk_server=idServer);
    DELETE FROM a_zone WHERE fk_server=idServer;
    DELETE FROM nfo_vm WHERE id IN (SELECT id FROM a_vm WHERE fk_server=idServer);
    DELETE FROM a_vm WHERE fk_server=idServer;
    DELETE FROM a_patch WHERE fk_server=idServer;
    DELETE FROM a_pkg WHERE fk_server=idServer;
    DELETE FROM a_net WHERE fk_server=idServer;
    DELETE FROM a_prj WHERE fk_server=idServer;
    DELETE FROM a_hba WHERE fk_server=idServer;
    DELETE FROM a_disk WHERE fk_server=idServer;
    DELETE FROM nfo_dataset WHERE id IN (SELECT id FROM a_dataset WHERE fk_pool IN (SELECT id FROM a_pool WHERE fk_server=idServer));
    DELETE FROM a_dataset WHERE id IN (SELECT id FROM a_pool WHERE fk_server=idServer);
    DELETE FROM a_pool WHERE fk_server=idServer;
    DELETE FROM a_rrd WHERE fk_server=idServer;
    DELETE FROM a_result WHERE fk_server=idServer;
    DELETE FROM a_nfss WHERE fk_server=idServer;
    DELETE FROM a_nfsm WHERE fk_server=idServer;
    DELETE FROM jt_server_sgroup WHERE fk_server=idServer;
END //
DELIMITER ;

--
-- Fetch first job
--
DELIMITER //
CREATE PROCEDURE getFirstJob
(idPID INT, OUT pID INT)
BEGIN
DECLARE record_not_found INT DEFAULT 0;
DECLARE vPid INT DEFAULT -1;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET record_not_found = 1;
DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;
DECLARE EXIT HANDLER FOR SQLWARNING ROLLBACK;
SET pID = 0;
START TRANSACTION;
SELECT id INTO pID FROM list_job WHERE state=1 ORDER BY id ASC LIMIT 0,1;
IF record_not_found THEN
  SET pID = 0;
ELSE
  UPDATE list_job SET state=2, fk_pid = idPid WHERE id = pID;
END IF;
COMMIT;
END //
DELIMITER ;

--
-- Lock Check
--
DELIMITER //
CREATE PROCEDURE lockCheck
(idPid INT, idServer INT, idCheck INT, OUT rc INT)
BEGIN
  DECLARE vLID INT DEFAULT 0;
  DECLARE record_not_found INT DEFAULT 0;
  DECLARE c1 CURSOR FOR SELECT id FROM list_lock WHERE fk_server = idServer AND fk_check = idCheck;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET record_not_found = 1;
  DECLARE EXIT HANDLER FOR SQLWARNING ROLLBACK;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;
  SET rc = 1;
  START TRANSACTION;
  OPEN c1;
  FETCH c1 INTO vLID;
  CLOSE c1;
  IF record_not_found THEN
    INSERT INTO list_lock(fk_pid, fk_server, fk_check, fct, t_add) VALUES(idPid, idServer, idCheck, '', UNIX_TIMESTAMP());
    SET rc = 0;
  ELSE
    SET rc = 2;
  END IF;
  COMMIT;
END //
DELIMITER ;

--
-- Unlock Check
--
DELIMITER //
CREATE PROCEDURE unlockCheck
(idServer INT, idCheck INT)
BEGIN
  DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;
  DECLARE EXIT HANDLER FOR SQLWARNING ROLLBACK;
  START TRANSACTION;
    DELETE FROM list_lock where fk_server = idServer AND fk_check = idCheck;
  COMMIT;
END //
DELIMITER ;


