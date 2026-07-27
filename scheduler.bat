@echo off

if not exist "D:\xampp\htdocs\nds_wip" echo Project folder not found & pause
if not exist "D:\xampp\php\php.exe" echo PHP not found & pause
if not exist "D:\temp" echo Temp folder not found & pause

cd /d "D:\xampp\htdocs\nds_wip"

echo %DATE% %TIME% > "D:\temp\schedule.log"

"D:\xampp\php\php.exe" artisan schedule:run >> "D:\temp\schedule.log" 2>&1