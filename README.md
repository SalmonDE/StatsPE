# StatsPE - Advanced Stats Plugin [![Travis-CI](https://travis-ci.org/SalmonGER/StatsPE.svg?branch=master)](https://travis-ci.org/SalmonGER/StatsPE)

### Information
**Note:** This plugin is not complete yet, there is some more coding to do!
This is the GitHub repository for developing a stats plugin, which uses JSON and MySQL to save it's data.
The plugin also contains an auto updater which checks if an update is available and downloads it if enabled in the config file.

---
***Installation*** | ***StatsPE***
-----------|-----------
Step 1 | Download the plugin [here](https://github.com/SalmonGER/StatsPE/releases/latest/) to get the latest pre-built phar!
Step 2 | After it has downloaded, drag the plugin into your **plugins** folder of your server files.
Step 3 | Start the server, and StatsPE has been added to your server!
*Optional* | If you want to disable a statistic from showing on `/stats [player]`, replace `true` with `false` in the config.
---
***MySQL Configuration*** | ***Using MySQL with StatsPE***
-----------|-----------
Step 4 | Put in your MySQL Settings in [these](https://github.com/SalmonGER/StatsPE/blob/master/resources/config.yml#L24-L29) lines.
Step 5 | Change the data providers from JSON to MySQL in [this](https://github.com/SalmonGER/StatsPE/blob/master/resources/config.yml#L20-L22) line.
---
***Command*** | ***Description*** | ***Permission Node***
-----------|-----------|-----------|
/stats [player] | Shows the player's stats, only for what is enabled in config.yml | statspe.cmd.stats
/stats [player] | Shows the player's stats along with advanced info, such as their ClientID and IP addresses | statspe.cmd.stats.advancedinfo
---
***Statistic*** | ***Description*** | ***Example*** | ***Type***
-----------|-----------|-----------|-----------|
PlayerName | Name of the player (case sensitive)| Aericio | Normal
ClientID | ClientID of the MCPE installation | -3504414010354 | Advanced
LastIP | Last used IP from the player | 192.168.178.42 | Advanced
FirstJoin | First time the player joined | 04:19:51 22.07.2016 (Configurable)| Normal
LastJoin | Last time the player joined | 04:23:01 22.07.2013 (Configurable)| Normal
JoinCount | How many times the player joined the server | 7 | Normal
KillCount | How often the player killed another player | 22 | Normal
DeathCount | How often the player died | 9 | Normal
KickCount | How often the player got kicked | 1 | Normal
OnlineTime | How long the player played on the server | WIP | To-Download
BlocksBreaked | How many blocks the player broke | 3 | Normal
BlocksPlaced | How many blocks the placer placed | 0 | Normal
ChatMessages | How many chat messages the player sent | 78 | Normal
FishCount | How many fishes the player catched | 1 | Normal
EnterBedCount | How often the player used a bed | 2 | Normal
EatCount | How often the player consumed an item | 13 | Normal
CraftCount | How often the player crafted something | 6 | Normal
---
