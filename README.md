# StatsPE - Advanced Stats Plugin [![Travis-CI](https://travis-ci.org/SalmonGER/StatsPE.svg?branch=master)](https://travis-ci.org/SalmonGER/StatsPE)

### Information
**Note:** This plugin is not complete yet, there is a lot more coding to do!
This is the GitHub repository for developing a stats plugin, which works with JSON/YAML and MySQL.

---
***Installation*** | ***Using JSON with StatsPE***
-----------|-----------
Step 1 | Download the plugin [here](https://github.com/SalmonGER/StatsPE/releases/latest/) to get the latest pre-built phar!
Step 2 | After it has downloaded, drag the plugin into your **plugins** folder of your server files.
Step 3 | Start the server, and StatsPE has been added to your server!
*Optional* | If you want to disable a stat from showing on `/stats [player]`, replace `true` with `false` in the config.
---
***Installation*** | ***Using MySQL with StatsPE***
-----------|-----------
Step 4 | Put in your MySQL Settings in [these](https://github.com/SalmonGER/StatsPE/blob/master/resources/config.yml#L24-L29) lines.
Step 5 | Change the data provideos from JSON to MySQL in [these](https://github.com/SalmonGER/StatsPE/blob/master/resources/config.yml#L20-L22) line.
---
***Command*** | ***Description*** | ***Permission Node***
-----------|-----------|-----------|
/stats [player] | Shows the player's stats, only for what is enabled in config.yml | statspe.cmd.stats
/stats [player] | Shows the player's stats along with advanced info, such as their ClientID and IP addresses | statspe.cmd.stats.advancedinfo
---
