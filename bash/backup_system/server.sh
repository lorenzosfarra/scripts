 #!/bin/bash

 # Copyright (C) 2012 Lorenzo Sfarra (lorenzosfarra@ubuntu.com)
 #
 # This program is free software; you can redistribute it and/or
 # modify it under the terms of the GNU General Public License
 # as published by the Free Software Foundation; either version 2
 # of the License, or (at your option) any later version.
 #
 # This program is distributed in the hope that it will be useful,
 # but WITHOUT ANY WARRANTY; without even the implied warranty of
 # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 # GNU General Public License for more details.
 #
 # You should have received a copy of the GNU General Public License
 # along with this program; if not, write to the Free Software
 # Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 #

##############################################################################
#########################  CONFIG - EDIT HERE ################################
######## PLEASE NOTE THAT THESE VALUES HAVE TO BE CONSISTENT WITH ############
########            THE CONFIG IN THE CLIENT SCRIPT               ############
##############################################################################

HOST="192.168.1.2"
PORT="7000"
# EMAIL CONFS

# This is the dir where the logs file are and where to store useful
# informations about, for example, the last backup time
BACKUP_DIR="$HOME/.custom_backup_dir"  
BACKUP_FILE="$BACKUP_DIR/backup_`date +"%Y%m%d"`.tar."



###########################   STOP EDIT HERE   ###############################

### ERRORS
ERROR_COPY_TOOLS=1
ERROR_COPY_TOOLS_MSG="ERROR: Unable to find your copy tool '$COPY_CMD'. Exiting..."

ERROR_TAR_TOOL=2
ERROR_TAR_MSG="ERROR: tar tool not found!. Exiting..."

ERROR_DIRS=3
ERROR_DIRS_MSG="[ERROR] BACKUP DIRS NOT EXSISTENT AND NOT CREABLE. Exit.\n\n"


TAR="/bin/tar"
TAR_PARAMS="c"
NETCAT="/bin/netcat"
SCP="/bin/scp"

DAYOFWEEK=`date +"%w"`
NOW=`date`

LOGS_DIR="$BACKUP_DIR/logs"
LOGS_FILE="$LOGS_DIR/log_`date +"%Y%m%d"`.log"

function verb_log {
  if $VERBOSE; then
    echo -en $1\\n;
  fi
}

# Redirect Output to a logfile and screen - Couldnt get tee to work
exec 3>&1                         # create pipe (copy of stdout)
exec 1>$LOGS_FILE                 # direct stdout to file
exec 2>&1                         # uncomment if you want stderr too
tail -f $LOGS_FILE >&3 &          # run tail in bg

# Do the given tools exits?
function tool_exists {
  verb_log "CHECKING TOOLS AVAILABILITY...\n\n"
  NETCAT=`which $COPY_CMD`
  if [ $? != 0 ]; then
    verb_log "\\t- NETCAT [NOT FOUND]\\n"
    if [ "$COPY_CMD" == "netcat" ]; then
      echo "FATAL, you have setted netcat as your copying tool."
      echo $ERROR_COPY_TOOLS_MSG
      exit $ERROR_COPY_TOOLS
    else
      verb_log "\\t not fatal...not your copying tool.\\n"
    fi
  else
    verb_log "\\t- NETCAT [found]\\n"
  fi
}


# BACKUP AND LOGS
function init_backup_and_logs {
  verb_log "CHECKING BACKUP AND LOGS DIRECTORIES..."
  mkdir -p $LOGS_DIR
  if [ $? != 0 ]; then
    echo $ERROR_DIRS_MSG
    exit $ERROR_DIRS
  else
    verb_log "[OK]\\n\\n"
  fi
}

# NETCAT COMMAND
function init_netcat_command {
  COMMAND="$NETCAT $HOST $PORT > $BACKUP_FILE"
}

echo $COMMAND
exit 0

