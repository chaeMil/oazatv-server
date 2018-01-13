# oazatv-server
Oáza.tv Server Application

This project is implementation of www.oaza.tv server,
including backend administration, frontend user website and json API for mobile apps to use.

##Main features of this system are:
  - ###already implenented or are worked on: 
    - **video archive**
      - uploading and managing video archive
      - converting videos to most used formats like mp4 or webm
      - cron executed video queue conversion
      - grabbing thumbnails from videos
      - simple video thumbnail editor
    - **photo archive**
      - uploading and managing photos in albums
      - automatically creating smaller thumbs
    - **user management**
      - creating users with rights
      - uploading custom avatars
    - **bug reporting system**
    - **system logging**
    
  - ###planned features:
    - simple analytics
    - private links to videos or photo albums
    - advanced photo editor
    - frontend menu manager
    - Google Cloud Messaging integration (for use in mobile apps)

###Frontend features:
*planned in future*

##Technical breakdown

The system is written in czech **PHP framework Nette** (2.3.4). Backend is using lots of **Bootstrap** 
and **jQuery** plugins. Upload is handled by resumable.js so even **files bigger than 2gb can be uploaded.**


#Instaling on your server or localhost
##Requirements
OS: **Linux 64bit** *(you can install it on Windows or OSX but you have to provide custom ffmpeg builds and rewrite little bit of code)*
Apache With PHP 5.4 and **exec command enabled!**

##Tested on
- Ubuntu 12.04 64bit with PHP 5.5
- Ubuntu 15.04 64bit with PHP 5.5
- Ubuntu 16.04 LTS 64bit with PHP 7.0

##Installing
1. git clone https://github.com/chaeMil/oazatv-server.git
2. cd oazatv-server/web-project
3. composer install
4. mkdir temp/cache
5. chmod 0777 temp/cache
6. mkdir www/db/videos
7. mkdir www/db/albums
8. chmod 0777 /www/db/*
9. mkdir www/uploded/avatars
10. chmod 0777 www/uploaded/avatars
11. mkdir www/temp/resumable-temp
12. chmod 0777 www/temp/resumable-temp

...

TODO!
