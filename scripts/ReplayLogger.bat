@echo off
dir C:\Games\World_of_Tanks\replays /B > %USERPROFILE%/Desktop/WOT_replays_%date:~6,4%%date:~3,2%%date:~0,2%.txt
exit