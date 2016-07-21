# StatsPE - Advanced Stats Plugin! [![Travis-CI](https://travis-ci.org/SalmonGER/StatsPE.svg?branch=master)](https://travis-ci.org/SalmonGER/StatsPE)

### Information
Note: This plugin is not complete yet, there is a lot more coding to do!
This is the GitHub repository for developing a stats plugin, which works with JSON/YAML and MySQL.
> NOTE FOR AERICIO: REDO README, IT LUKS UNPRUFESHIONUL (aka just rewrite it, maybe using tables instead.)

### Installation Process (Adding the Plugin, without MySQL)

1. Download the plugin [here](https://github.com/SalmonGER/StatsPE/releases/latest/) to get the latest pre-built phar!
2. After it has downloaded, drag the plugin into your **plugins** folder of your server files.
3. Start the server, and StatsPE has been added to your server!
4. If you want to disable a module from showing on `/stats [player]`, go to the configuration and replace `StatCount: True` with `StatCount: False`

##### If you would like to use MySQL, do this!

5. Put in your MySQL Database information in this area:

```
# MySQL Settings (Only configure this if you are going to use MySQL data provider)
address: "MySQL.example.net"
port: 3306
user: "ExampleUser"
password: "12345678"
database: "StatsPE"
```
6. Go into the configuration file of the Plugin, and replace JSON with MySQL

```
# Set default data provider for StatsPE
# Available Providers: MySQL, JSON (Use this if you want YAML)
Provider: JSON
```
