![Alt text](https://salmonde.de/MCPE-Plugins/Pictures/StatsPE/StatsPE.png "StatsPE Icon")

# StatsPE - Advanced Stats Plugin [![Travis-CI](https://travis-ci.org/SalmonGER/StatsPE.svg?branch=master)](https://travis-ci.org/SalmonGER/StatsPE)

## Information

**Want to test the features before using them? Test them on this server: salmonde.de:19132**

**_Installation_** | **_StatsPE_**
------------------ | -------------------------------------------------------------------------------------------------------------------
Step 1             | Download the plugin [here](https://github.com/SalmonGER/StatsPE/releases/latest/) to get the latest pre-built phar!
Step 2             | After it has downloaded, drag the plugin into your **plugins** folder of your server files.
Step 3             | Start the server, and StatsPE has been added to your server!
_Optional_         | If you want to disable a statistic from showing on `/stats [player]`, replace `true` with `false` in the config.

--------------------------------------------------------------------------------

**_MySQL Configuration_** | **_Using MySQL with StatsPE_**
------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------
Step 4                    | Put your MySQL Settings in [these](https://github.com/SalmonGER/StatsPE/blob/master/resources/config.yml#L41-L45) lines.
Step 5                    | Change the data providers from JSON to MySQL in [this](https://github.com/SalmonGER/StatsPE/blob/master/resources/config.yml#L38) line.

--------------------------------------------------------------------------------

**_Command_**                | **_Description_**                                                                          | **_Permission Node_**
---------------------------- | ------------------------------------------------------------------------------------------ | ------------------------------
/stats [player]              | Shows the player's stats, only for what is enabled in config.yml                           | statspe.cmd.stats
/stats [player]              | Shows the player's stats along with advanced info, such as their ClientID and IP addresses | statspe.cmd.stats.advancedinfo
/floatingstats add [name]    | Adds a floatingstat to your current person with a name specified by you                    | statspe.cmd.floatingstats
/floatingstats list          | Lists all floatingstats on the server                                                      | statspe.cmd.floatingstats
/floatingstats remove [name] | Removes a floatingstat                                                                  | statspe.cmd.floatingstats
/fixtable                    | Tries to add or modify columns to an existing table (MySQL only!)                          | statspe.cmd.fixtable

--------------------------------------------------------------------------------

**FloatingStat Setting** | **Description**                                                                                       | **Example**
------------------------ | ----------------------------------------------------------------------------------------------------- | ------------------------------------------------
Name                     | The name of the floating stat, specified by the creator                                               | Spawn
Enabled                  | Specifies if the floating stat should be enabled (must be true or false)                              | false
Position                 | Contains information about the position of the floating stat (Array)                                  | X => '100' Y => '50' Z => '400' Level => 'Lobby'
PlayerName               | If NOT false the associated floating stat will show the stats of the player specified in this setting | SalericioDE
Stats                    | Contains settings to disable or enable single statistics for the floatingstat (Array)                 | KillCount => true DeathCount => false

--------------------------------------------------------------------------------

**_Statistic_**   | **_Description_**                             | **_Example_**                      | **_Type_**
----------------- | --------------------------------------------- | ---------------------------------- | ----------
PlayerName        | Name of the player (case sensitive)           | SalericioDE                        | Normal
ClientID          | ClientID of the MCPE installation             | -8655314918531                     | Advanced
XBoxAuthenticated | If the user is authenticated with Xbox or not | true                               | Advanced
LastIP            | Last used IP from the player                  | 192.168.1.45                       | Advanced
UUID              | Player's UUID                                 | 3942e063-fa8f-3a43-8fc2-201dc6     | Advanced
FirstJoin         | First time the player joined                  | 04:19:51 22.07.2016 (Configurable) | Normal
LastJoin          | Last time the player joined                   | 04:23:01 22.07.2016 (Configurable) | Normal
JoinCount         | How many times the player joined the server   | 10                                 | Normal
KillCount         | How often the player killed another player    | 69                                 | Normal
DeathCount        | How often the player died                     | 9                                  | Normal
K/D               | Player's Kill/Death Ratio                     | 38%                                | Normal
KickCount         | How often the player got kicked               | 1                                  | Normal
OnlineTime        | How long the player played on the server      | WIP                                | To-Do
BlocksBreaked     | How many blocks the player broke              | 3                                  | Normal
BlocksPlaced      | How many blocks the placer placed             | 4                                  | Normal
ChatMessages      | How many chat messages the player sent        | 78                                 | Normal
FishCount         | How many fishes the player catched            | 1                                  | Normal
EnterBedCount     | How often the player used a bed               | 2                                  | Normal
EatCount          | How often the player consumed an item         | 13                                 | Normal
CraftCount        | How often the player crafted something        | 6                                  | Normal
Items dropped     | How many items the player dropped             | 294                                | Normal
Money [EconomyAPI, PocketMoney] | How much money person has       | $4221                              | Normal
--------------------------------------------------------------------------------
