###########################################################################
# Copyright (c) 2007 Lorenzo Sfarra                                       #
#                                                                         #
# This program is free software; you can redistribute it and/or modify    #
# it under the terms of version 2 of the GNU General Public License as    #
# published by the Free Software Foundation.                              #
#                                                                         #
# This program is distributed in the hope that it will be useful,         #
# but WITHOUT ANY WARRANTY; without even the implied warranty of          #
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           #
# GNU General Public License for more details.                            #
#                                                                         #
###########################################################################

import sys
import sqlite
import traceback
from datetime import datetime
now = datetime.now

input_db = "Factoids.db"                        # Factoids database
output_db = "ubuntu-it.db"                      # Encyclopedia database's name
editors_name = "ubotit_script"                  # Default editor's name

query = """
  CREATE TABLE facts (
        id INTEGER PRIMARY KEY,
        author VARCHAR(100) NOT NULL,
        name VARCHAR(20) NOT NULL,
        added DATETIME,
        value VARCHAR(200) NOT NULL,    
        popularity INTEGER NOT NULL DEFAULT 0
);

  CREATE TABLE log (
        id INTEGER PRIMARY KEY,
        author VARCHAR(100) NOT NULL,
        name VARCHAR(20) NOT NULL,
        added DATETIME,
        oldvalue VARCHAR(200) NOT NULL
);"""


def LOG(message):
  """Prints the given message."""
  # Maybe you want to do other things here... (?)
  print message


db1 = sqlite.connect(input_db)                  # open the database
cursor1 = db1.cursor()                          # get the cursor
db2 = sqlite.connect(output_db)
cursor2 = db2.cursor()

try:
  cursor1.execute("""
    SELECT keys.key, factoids.fact
      FROM keys, factoids
     WHERE factoids.key_id = keys.id""")        # get all the pairs key->value
except sqlite.DatabaseError:
  LOG("""
Database (input) error, 'SELECT'.
A possible reason is that the input database doesn't exist,
or that it has not the right structure.
Complete error message:\n%s""" 
      %traceback.print_last())

  sys.exit(1)                                   # we can't go on, exit.

keys_values = cursor1.fetchall()                # put items in the array

cursor2.execute(query)                          # create the Ency. db's struct.
db2.commit()

# Add items to the new database!
for key, value in keys_values:
  try:
    cursor2.execute("""
  INSERT INTO facts (name, value, author, added) 
    VALUES (%s, %s, %s, %s)""",
    (key, value, editors_name, str(now())))
  except sqlite.DatabaseError:
    LOG("""
Database (output) error, 'INSERT'.
Complete error message:\n%s"""
        %traceback.print_last())
    sys.exit(2)                                 # we can't go on, exit.
db2.commit()

# Close connections
db1.close()
db2.close()

# Print some statistics
LOG ("""
%s
%-20s %s
%-20s %s
%-20s %d""" %("ALL OK",
              "Database input",
              input_db,
              "Database output",
              output_db,
              "Voci trasferite",
              len (keys_values)))

