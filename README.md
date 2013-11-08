Dylos Air Particule Counter Experimentation
=====

Scripts for the dylos reader experimentation, explained at 
<a href='http://aqicn.org/faq/2013-09-08/dylos-air-particule-counter-experimentation-part-1/'>
aqicn.org/.../dylos-air-particule-counter-experimentation/</a>

Preconditions
----

In order to run the dylos experiment, you will need the following Hardware:

- Dylos DC1100 pro, with Serial Interface (or DC1700)
- Raspbery PI
- USB to serial cable

The SW configuration is as follow:

- Standard PI Raspbian OS (http://www.raspberrypi.org/downloads)
- Additional packages: PHP (```sudo apt-get install php5```)

Installation
----

Copy the content of the folder ```php-scripts``` to the local folder, and edit the file ```conf.php```:

- Edit the line ```static $sensorId = "xxx";``` and replace ```xxx``` with your ID. You can use any ID, and we suggest you use your email address, or anything similar that can uniquely identify yourself. 

- If needed, edit the line ```static $serialPort  = "/dev/ttyUSB0";```, and replace the ```/dev/ttyUSB0``` with your serial port configuration.


Running the scripts
----

``cd`` to the PHP script directory, and then just run ```php main.php```.

If you encounter problem with the ```openbase_dir``` such as "PHP Warning:  fopen(): open_basedir restriction in effect", you can use the command line ```php -d open_basedir=none main.php``` instead. 

If you want the dylos script to be started automatically during PI boot strap, just ```sudo vi /etc/rc.local``` and add line ```nohup php /path-to-your-dylos-script/main.php > /dev/null 2>&1&``` at the end of the file


To Do list
----

For now, only PHP scripts are available. If you'd like to contribute, feel free to port it to any other language (e.g. python..) 



Credits
---

The Serial port php reader is provided by https://code.google.com/p/php-serial/

