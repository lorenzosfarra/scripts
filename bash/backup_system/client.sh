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
########            THE CONFIG IN THE SERVER SCRIPT               ############
##############################################################################

REMOTE_ADDR="192.168.1.2"
REMOTE_PORT="7000"
# COPY_CMD could be scp or netcat. netcat is the default: fast but not secure.
COPY_CMD="netcat"
# EXCLUDE VERSION CONTROL files and dirs from the backup (svn, git, bzr,...)?
EXCLUDE_VCS=true
# FILTER OUTPUT: could be bzip2 or gzip
FILTER_OUTPUT="bzip2"
VERBOSE=true
# EMAIL CONFS
EMAIL_SEND=true # True if you want an email to let you know backup status
EMAIL_ADDR=lorenzosfarra@gmail.com
EMAIL_SUBJECT=BACKUP

# SECURE-RELATED INFO - KEYS NOT SUPPORTED FOR NOW
USERNAME=user
PASSWORD=pass

# This is the dir where the logs file are and where to store useful
# informations about, for example, the last backup time
BACKUP_DIR="$HOME/.custom_backup_dir"  
BACKUP_FILE="$BACKUP_DIR/backup_`date +"%Y%m%d"`.tar."

# WHAT TO BACKUP?
BACKUP_TARGET="/home/lorenzo/projects /home/lorenzo/Documents"


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
  # TAR
  TAR=`which tar`
  if [ $? != 0 ]; then
    verb_log "\\t- TAR [NOT FOUND]\\n"
    echo $ERROR_TAR_MSG;
    exit $ERROR_TAR_TOOL;
  else
    verb_log "\\t- TAR [found]\\n"
  fi
  # COPY TOOLS
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
  SCP=`which $COPY_CMD`
  if [ $? != 0 ]; then
    verb_log "\t- SCP [NOT FOUND]\\n"
    if [ "$COPY_CMD" == "scp" ]; then
      echo "FATAL, you have setted scp as your copying tool."
      echo $ERROR_COPY_TOOLS_MSG
      exit $ERROR_COPY_TOOLS
    else
      verb_log "\\t not fatal...not your copying tool.\\n"
    fi
  else
    verb_log "\t- SCP [found]\\n"
  fi
}

# SET tar params and options
function set_tar_params {
  if $VERBOSE; then
    TAR_PARAMS="$TAR_PARAMS"v
  fi
  if [ $FILTER_OUTPUT == "bzip2" ]; then
    TAR_PARAMS="$TAR_PARAMS"j
    BACKUP_FILE="$BACKUP_FILE"bz2
  elif [ $FILTER_OUTPUT == "gzip" ]; then
    TAR_PARAMS="$TAR_PARAMS"z
    BACKUP_FILE="$BACKUP_FILE"gz
  else
    echo "[WARNING] Unsupported output filter...back to default: bzip2"
    TAR_PARAMS="$TAR_PARAMS"j
  fi
  TAR_PARAMS="$TAR_PARAMS"f
  if $EXCLUDE_VCS; then
    TAR_PARAMS="--exclude-vcs $TAR_PARAMS"
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
  COMMAND="$COMMAND | $NETCAT $REMOTE_ADDR $REMOTE_PORT"
}

# SCP COMMAND
function init_scp_command {
  COMMAND="$COMMAND && $SCP -p $REMOTE_PORT $USERNAME@$REMOTE_ADDR $BACKUP_FILE $COMMAND"
}

# TAR COMMAND
function init_tar_command {
  if [ "$COPY_CMD" == "netcat" ]; then
    COMMAND="$TAR $TAR_PARAMS - $BACKUP_TARGET"
    init_netcat_command
  else
    COMMAND="$TAR $TAR_PARAMS $BACKUP_FILE $BACKUP_TARGET"
    init_scp_command
  fi
}


echo "******************* BACKUP **********************"
echo "HOSTNAME: `hostname`"
echo -e "START TIME: `date`\\n\\n\\n\\n"


# CHECK TOOLS AVAILABILITY
tool_exists
# SET tar PARAMS
set_tar_params
# DIRS
init_backup_and_logs

COMMAND=""
init_tar_command

echo $COMMAND
exit 0

