# VFront
*VFront is a free, open source front-end for MySQL or PostgreSQL databases written in PHP and Javascript.*

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)

## Quickstart

In your server, e.g on /var/www/html/

```bash
# Open your webserver directory, e.g. 
# cd /var/www/html 

# Clone the repository
git clone git@github.com:marciuz/vfront.git
cd vfront 

# Set the permission to the filesystem: on Debian / Ubuntu  
chown -R www-data: ./conf ./files

# or on CentOS / Redhat based
chown -R apache: ./conf ./files
```


Open the installation script on your browser and start the visual installation.
When you open - for example http://localhost/vfront/ you will be redirected to http://localhost/vfront/_install/

![alt text](https://raw.githubusercontent.com/marciuz/vfront-docs/master/VFront_Installer.png?sanitize=true&raw=true)
