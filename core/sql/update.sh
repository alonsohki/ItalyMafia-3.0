#!/bin/sh
cat cleanup.sql account.sql vehicle.sql faction.sql stats.sql house.sql room.sql houses_data.sql beach_rooms.sql vinewood_rooms.sql data.sql | mysql -uroot -p italymafia
