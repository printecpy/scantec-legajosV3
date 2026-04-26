<?php print_r((new PDO("mysql:host=localhost;dbname=expdb;charset=utf8", "root", ""))->query("SHOW TABLES LIKE '%legajo%'")->fetchAll(PDO::FETCH_COLUMN));
