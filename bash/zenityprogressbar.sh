#!/bin/sh
#
# Author: Lorenzo Sfarra (C) 2013 <lorenzosfarra@ubuntu.com>
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU 3 General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

# DEFAULT VALUES
SECONDS=10
SLEEPTIME=0.1

### HELP FUNCTION ###
showhelp() {
  echo "HELP: show a simply progress bar."
  echo
  echo "USAGE: $0 [-h] [-s seconds]"
  echo
  echo -e "\t-h show this help message."
  echo -e "\t-s seconds adapt the progress bar to finish in this amount of time."
  echo
}

### SET TIME VALUES ###
settimevalues() {
  # Check if this is a number
  expr $1 + 1 &>/dev/null
  if [ $? -ne 0 ]; then
    echo "the value has to be in seconds!"
    exit 1
  fi
  SECONDS=$1
  SLEEPTIME=$(echo "scale=4; $SECONDS/100" | bc)
  echo "SLEEPTIME IS $SLEEPTIME"
}

# PARSE ARGUMENTS
while getopts "s:h" opt; do
  case "$opt" in
    h)
      showhelp
      exit 0
      ;;
    s)
      settimevalues $OPTARG
      ;;
  esac
done

percentage=0; 
while [ "$percentage" -lt 100 ];
do 
  echo "$percentage"
  percentage=$((percentage+1)); 
  sleep $SLEEPTIME; 
done | zenity --text "Progress bar example in progress..." --title "Scripts example..." --progress

