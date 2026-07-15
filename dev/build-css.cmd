@echo off
REM Rebuild the static Tailwind CSS after editing views. Run from project root.
cd /d "G:\IT Training"
dev\bin\tailwindcss.exe -c tailwind.config.js -i resources\css\app.css -o public\assets\css\app.css --minify
REM Keep the admin docroot's copy in sync (admin.itti.com.pk is a separate docroot).
copy /Y public\assets\css\app.css admin\assets\css\app.css >nul
copy /Y public\assets\img\logo.jpg admin\assets\img\logo.jpg >nul
copy /Y public\assets\img\favicon.svg admin\assets\img\favicon.svg >nul
echo CSS rebuilt and synced to admin.
