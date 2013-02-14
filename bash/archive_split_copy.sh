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

# Reset in case getopts has been used previously in the shell.
OPTIND=1

# Our variables
inputfilename=""
archivefile=""
partfileprefix="part-"
quiet=0
size=50
sizeunit=m

### HELP ###
# Show an help message (strange, uh?)
showhelp() {
  echo
  echo "Usage: $0 [-i inputfilename] -a archivefilename [-p partfileprefix] [-q] [-h] [-m size]"
  echo
  echo -e "\t-i inputfilename is the name of the file(s) to put inside the archive."
  echo -e "\t-a archivefilename is the name of the archive to [create/]split."
  echo -e "\t-p partfileprefix is the prefix of each part when the archive is splitted (es.: part-aa, part-ab, etc..)."
  echo -e "\t-q for quiet mode."
  echo -e "\t-h for this help."
  echo -e "\t-m size size of each part."
  echo -e "\t-s size unit (m or k -> mega or kilo)."
}

### PREMISE ###
# Some infos about what we'll do and with wich arguments
premise() {
  echo
  if [ -z $inputfilename ]; then
    echo "Nothing to compress, simply split an archive."
  else
    echo "Creating an archive for $inputfilename."
  fi
  echo "Archive name:        $archivefilename."
  echo "Prefix of each part: $partfileprefix."
  echo "Size of each part:   $size."
  echo "Starting...."
  echo
}

### CECKS IF ARGUMENTS ARE VALID ###
argscheck() {
  errors=0
  # size has to be a number!
  expr $size + 1 &>/dev/null
  if [ $? -ne 0 ]; then
    echo "ERROR: 'size' has to be a number"
    errors=1
  fi
  # Check that the only required arguments it's used. To check it,
  # simply check that the $archivefilename variable is not empty.
  if [ -z "$archivefilename" ]; then
    echo "ERROR: 'archivefilename' is required"
    errors=1
  fi
  # If the input file(s) is empty, then the archive has to exist and viceversa
  if [ -z "$inputfilename" ]; then
    if [ ! -f "$archivefilename" ]; then
      echo "ERROR: inputfilename not specified and archivefilename doesn't exist."
      errors=1
    fi
  else
    if [ -f "$archivefilename" ]; then
      echo "ERROR: $archivefilename already exists."
      errors=1
    fi
    # TODO: CHECK THAT THE INPUT FILE(S) EXIST(S)
  fi
  if [ $errors -eq 1 ]; then
    showhelp
    echo
    echo "CHECK THE ERRORS!"
    exit 1
  fi
}


####################################
##### ARGS PARSING WITH GETOPTS ####
while getopts "qi:p:a:m:s:h" opt; do
  case "$opt" in
    h)
      showhelp
      exit 0
      ;;
    q)
      quiet=1
      ;;
    i)
      inputfilename=$OPTARG
      ;;
    a)
      archivefilename=$OPTARG
      ;;
    m)
      size=$OPTARG
      ;;
    s)
      sizeunit=$OPTARG
      ;;
    p)
      partfileprefix=$OPTARG
      ;;
    esac
done

shift $((OPTIND-1))

[ "$1" = "--" ] && shift

### SIMPLY USED FOR PRINTING STEPS ###
stepcnt=1
step() {
  if [ $quiet -eq 1 ]; then
    echo "STEP $stepcnt: $1"
    echo
  fi
  stepcnt=$(($stepcnt + 1))
}

### DEBUG ###
debug() {
  if [ $quiet -eq 1 ]; then
    echo $1
  fi
}

### CREATE ARCHIVE ###
createarchive() {
  tar -jvcf $archivefilename $inputfilename
  if [ $? -ne 0 ]; then
    echo "ERROR: Impossible to create the archive."
    exit 2
  fi
}

### SPLIT ARCHIVE ###
splitarchive() {
  split -b $size$sizeunit "$archivefilename" "$partfileprefix"
  if [ $? -ne 0 ]; then
    echo "ERROR: Impossible to split the archive."
    exit 3
  fi
}

### FINAL INSTRUCTIONS ###
instructions() {
  echo "NOW:"
  echo -e "\t1) copy al the pieces to your remote machine."
  echo -e "\t2) connect to the remote machine."
  echo -e "\t3) change the current directory to the one that contains the copied pieces."
  echo -e "\t4) launch the following command: cat $partfileprefix* > $archivefilename"
}

### ARGS CHECK ###
step "Checking arguments..."
argscheck
### SOME INFO  ###
if [ $quiet -eq 1 ]; then
  premise
fi
### CREATE ARCHIVE? ###
if [ -n $inputfilename ]; then
  step "Creating the archive..."
  createarchive
fi
### SPLIT THE ARCHIVE ###
step "Splitting the archive..."
splitarchive

### instructions to how to rebuild the archive from his pieces
step "NOW IT'S UP TO YOU"
instructions
