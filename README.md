# [Food Grindr](https://github.com/jctrvlr/inclass_work_490)

**Installing from Github:**

1. git clone [https://github.com/jctrvlr/inclass\_work\_490.git](https://github.com/jctrvlr/inclass_work_490.git)
2. git checkout master

This will replicate the repository with all files that are needed. A future user should create a directory called &#39;inclass\_work\_490&#39; to store the data.

**Setting up the System:**

Each of these servers are necessary for the recreation of this project. Future users should follow the specific instructions exactly. Also servers should not be hosted on the same machines. If a machine is lost, down, broken, etc., the project and team will not be able to run and execute. To be sure this does not happen, divide the servers among different machines and members of the team.

* Production Servers
  1. Frontend: Prod-fe - 192.168.2.21
    1. Install apache2, php7.0, php-amqp, php7.0-mysql, git with apt-get.
      1. Configure php7.0 but navigating to /etc/php/7.0/apache2.0/php.ini and add extension=amqp.so to the end of the file.
    2. HTML files located in /var/www/html/
    3. It is a Apache2 Load balancer that is located on the deployServer that uses Prod-fe, and HSB-Fe.
    4. Make sure IP is changed to correct prod-be IP in .ini files.
  2. Backend: Prod-be - 192.168.2.20
    1. Install php7.0, php-qp, rabbitmq-server, mysql-server.
      1. Configure php7.0 but navigating to /etc/php/7.0/apache2.0/php.ini and add extension=amqp.so to the end of the file.
    2. Download and start rabbitMQServer.php, rabbitMQServerInvite.php, rabbitMQServerReview.php, rabbitMQServerData.php.
    
  3. DMZ: Prod-dmz - 192.168.2.8
    1. Install php7.0, php-qp, php7.0-mysql with apt-get
      1. Configure php7.0 but navigating to /etc/php/7.0/apache2.0/php.ini and add extension=amqp.so to the end of the file.
    2. Navigate to /git/inclass\_work\_490/php\_backend
    3. execute : ./rabbitMQServerBackend.php

1. Quality Assurance Servers
  1. Frontend: Qa-fe - 192.168.2.16
    1. HTML files located in /var/www/html/
    2. It is a Apache2 Load balancer that is located on the deployServer that uses Prod-fe, and HSB-Fe.
    3. Make sure IP is changed to correct prod-be IP in .ini files.
  2. Backend: Qa-be - 192.168.2.14
    1. Download and start rabbitMQServer.php, rabbitMQServerInvite.php, rabbitMQServerReview.php, rabbitMQServerData.php.
  3. DMZ: qa-dmz - 192.168.2.13
    1. Navigate to /git/inclass\_work\_490/php\_backend
    2. execute : ./rabbitMQServerBackend.php

1. Development Servers
  1. Frontend: dev-fe - 192.168.2.5
    1. Install apache2, php7.0, php-amqp, github.com
    2. HTML files located in /var/www/html/ - It is a Apache2 Load balancer that is located on the deployServer that uses Prod-fe, and HSB-Fe. Make sure IP is changed to correct prod-be IP in .ini files.
  2. Backend: dev-be - 192.168.2.17
    1. Start rabbitMQServer.php, rabbitMQServerInvite.php, rabbitMQServerReview.php, rabbitMQServerData.php.
  3. DMZ: dev-dmz - 192.168.2.9
    1. Navigate to /git/inclass\_work\_490/php\_backend
    2. execute : ./rabbitMQServerBackend.php

1. Deployment Server
  1. deploy - 192.168.2.11
    1. Navigate to /inclass\_work\_490/deploy
    2. Execute ./deployServer.php
    3. Navigate to /etc/apache2/sites-available/
    4. Edit 000-default.conf to update IP&#39;s

***
