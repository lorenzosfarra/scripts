#!/bin/bash
# This file is part of Zenity Player.
# Author: Lorenzo Sfarra <lorenzosfarra@ubuntu.com>
# 
# Zenity Player is free software: you can redistribute it and/or modify
# it under the terms of the GNU  General Public License as published by
# the Free Software Foundation, either version  2f the License, or
# (at your option) any later version.
# 
# Zenity Player is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with Zenity Player.  If not, see <http://www.gnu.org/licenses/>.
# 
# Copyright (C) 2009 Lorenzo Sfarra <lorenzosfarra@ubuntu.com>

# Rhythmobx is running?
RHYTHMBOXPID="`pidof rhythmbox`"
if [ "x$RHYTHMBOXPID" == "x" ]; then
  # Launch rhythmbox...
  rhythmbox &
else
  echo "Found a rhythmbox instance with PID: $RHYTHMBOXPID";
fi

RHYTHMBOX="rhythmbox-client --no-start"

# ACTIONS AND INFOS
commands=( Pause Play Next Previous Info Details Close )
actionslist=""
command="zenity --title Player --list --radiolist --column=* --column=Action "

# Add the commands to the list
for act in ${commands[@]}
do
  actionslist="$actionslist $act $act"
done
command="$command $actionslist --height 300"


# Infinite loop
while [ 1 ]; do
  action="`$command`"
  case "$action" in
    Pause)
      $RHYTHMBOX --pause
      ;;
    Play)
      $RHYTHMBOX --play
      ;;
    Next)
      $RHYTHMBOX --next
      ;;
    Previous)
      $RHYTHMBOX --previous
      ;;
    Info)
      # Display track name and track artist
      playing="`$RHYTHMBOX --print-playing`"
      zenity --info --title "Now playing..." --text "$playing"
      ;;
    Details)
      # A detailed list of info about the currently played song
      playing="`$RHYTHMBOX --print-playing-format "%tt (%td) from %at (%ay, %ag) by %ta"`"
      zenity --info --title "Details about the current track" --text "$playing"
      ;;
    Close)
      $RHYTHMBOX --quit
      break;
      ;;
    *)
      break;
      ;;
  esac
done

#vim: ai ts=2 sw=2 et sts=2
