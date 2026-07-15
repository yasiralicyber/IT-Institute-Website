@echo off
set "PHP=C:\Users\k  H  a  N\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
"%PHP%" -c "G:\IT Training\dev\php.ini" -S 127.0.0.1:8090 -t "G:\IT Training\public" "G:\IT Training\public\router-dev.php"
