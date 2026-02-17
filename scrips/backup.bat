@echo off
net stop CobianBackup11
timeout /t 2 /nobreak >nul
"C:\Program Files (x86)\Cobian Backup 11\Cobian.exe" -bu "backupScantec"
timeout /t 5 /nobreak >nul
net start CobianBackup11
