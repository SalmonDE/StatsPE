# StatsPE - Advanced Stats Plugin [![Travis-CI](https://travis-ci.org/SalmonGER/StatsPE.svg?branch=master)](https://travis-ci.org/SalmonGER/StatsPE)

## Information

**Note:** MySQL support is done but we need testers since this feature is very new.<br>

## Feel free to request any features you would like the plugin to have [here](https://github.com/SalmonGER/StatsPE/issues/1)

**_Installation_** | **_StatsPE_**
------------------ | -------------------------------------------------------------------------------------------------------------------
Step 1             | Download the plugin [here](https://github.com/SalmonGER/StatsPE/releases/latest/) to get the latest pre-built phar!
Step 2             | After it has downloaded, drag the plugin into your **plugins** folder of your server files.
Step 3             | Start the server, and StatsPE has been added to your server!
_Optional_         | If you want to disable a statistic from showing on `/stats [player]`, replace `true` with `false` in the config.

--------------------------------------------------------------------------------

**_MySQL Configuration_** | **_Using MySQL with StatsPE_**
------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------
Step 4                    | Put in your MySQL Settings in [these](https://github.com/SalmonGER/StatsPE/blob/master/resources/config.yml#L24-L29) lines.
Step 5                    | Change the data providers from JSON to MySQL in [this](https://github.com/SalmonGER/StatsPE/blob/master/resources/config.yml#L20-L22) line.

--------------------------------------------------------------------------------

**_Command_**   | **_Description_**                                                                          | **_Permission Node_**
--------------- | ------------------------------------------------------------------------------------------ | ------------------------------
/stats [player] | Shows the player's stats, only for what is enabled in config.yml                           | statspe.cmd.stats
/stats [player] | Shows the player's stats along with advanced info, such as their ClientID and IP addresses | statspe.cmd.stats.advancedinfo

--------------------------------------------------------------------------------

**_Statistic_** | **_Description_**                           | **_Example_**                      | **_Type_**
--------------- | ------------------------------------------- | ---------------------------------- | -----------
PlayerName      | Name of the player (case sensitive)         | SalericioDE                        | Normal
ClientID        | ClientID of the MCPE installation           | -8655314918531                     | Advanced
XBoxAuthenticated | If the user is authenticated with Xbox or not | true                           | Advanced
LastIP          | Last used IP from the player                | 192.168.1.1                        | Advanced
UUID            | Player's UUID                               | 3942e063-fa8f-3a43-8fc2-201dc6     | Advanced
FirstJoin       | First time the player joined                | 04:19:51 22.07.2016 (Configurable) | Normal
LastJoin        | Last time the player joined                 | 04:23:01 22.07.2016 (Configurable) | Normal
JoinCount       | How many times the player joined the server | 10                                 | Normal
KillCount       | How often the player killed another player  | 69                                 | Normal
DeathCount      | How often the player died                   | 9                                  | Normal
KickCount       | How often the player got kicked             | 1                                  | Normal
OnlineTime      | How long the player played on the server    | WIP                                | To-Do
BlocksBreaked   | How many blocks the player broke            | 3                                  | Normal
BlocksPlaced    | How many blocks the placer placed           | 4                                  | Normal
ChatMessages    | How many chat messages the player sent      | 78                                 | Normal
FishCount       | How many fishes the player catched          | 1                                  | Normal
EnterBedCount   | How often the player used a bed             | 2                                  | Normal
EatCount        | How often the player consumed an item       | 13                                 | Normal
CraftCount      | How often the player crafted something      | 6                                  | Normal

--------------------------------------------------------------------------------
