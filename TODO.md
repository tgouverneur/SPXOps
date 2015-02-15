* UR == User Request

Urgent:
========

 - Implement regression testing

Medium:
========

 - find a way to inventorize custom metrics (might be implemented through plugins)
 - [UR] inventorize KVM VNC ports and add link to KVM VNC console on VM page
 - Replace mail() call with something like Pear::Mail_Queue
 - Send notification from frontend action (without slowing the whole thing down...)
 - Add a logfile for exception cought in the code...

Cosmetics:
========

 - Add a check_config table to configure the variables of checks like minimal versions and so on...
 - Move away from MD5 for passsword hash, use maybe openwall phpass or : https://crackstation.net/hashing-security.htm#phpsourcecode

Nice to have:
========

 - Fix check_ifaces for HME duplex check
 - Switch/online/offline resource group with web gui
 - Integration of linux clusters?
 
Linux checks:
========

 - fragmentation / inodes left check for every fs
 - diff of /etc/* plaintext files
 - rpm -qVa
 - proper sudo access enforcement
 - permitrootlogin a NO
