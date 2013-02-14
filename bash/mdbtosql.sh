#!/bin/bash
# MDBTOMySQL script
# Author: Lorenzo Sfarra <lorenzosfarra@ubuntu.com>
# License: GPLv3 <http://www.gnu.org/licenses/gpl-3.0.txt>
# 
# INFO: mdbtools needed!

# Enough params?
if [ $# -lt 2 ]; then
  echo "Usage: $0 input_file.mdb outfile.sql"
  exit 3
fi

# The input file .mdb exists?
if [ ! -f $1 ]; then
  echo "$1 not found."
  exit 2
fi

# Echo tables'list?
echo -n "List ALL the tables before exporting them?[y/N] "
read answer
if [ " $answer" == " Y" ] || [ " $answer" == " y" ]; then
  echo "LIST OF TABLES:"
  echo "               \n==========================================\n\n"
  mdb-tables $1;
  if [ $? != 0 ]; then
    echo "[ERROR] Unable to retrieve list of tables!";
  fi
  echo "               \n==========================================\n\n"
fi

# Convert the schema
echo "-- SCHEMA:\n\n" > $2
mdb-schema $1 >> $2
if [ $? != 0 ]; then
  echo "[ERROR] Unable to retrieve the DB schema from $1."
  exit 1
fi
echo "[SUCCESS] schema successfully exported in $2."

# Get the data from tables
echo "-- TABLES DATA:\n\n" >> $2
for table in $(mdb-tables $1);
do
  mdb-export -I $1 $table | sed -e 's/)$/)\;/' >> $2
  if [ $? != 0 ]; then
    echo "[ERROR] error exporting table '$table'..."
  fi
done
echo "Done."
