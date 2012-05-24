#!/usr/bin/env python

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


import os
import sys
import argparse
import fnmatch
from commands import getoutput
from shutil import move as shmove
import gettext

_ = gettext.gettext

class PrependSheBang:
  """ Class to prepend a shebang to the files you want. """

  interpreters = [
    "python",
    "perl", 
    "sh",
    "bash",
    "dash",
    "php"
  ]

  path = None
  target = "*.txt"

  default_lang = "python"
  
  # the status of the prepend operation
  prepend_shebang_status = False

  def __init__(self, path, interpreter, target):
    """
    Class contructor.
    @param str path the path where to start the loop
    @param str interpreter what progr. we have to add shebang for
    @param str target what files to modify? (Ex.: *.py, *.c, *)
    """
    errors = False
    #Path
    if not os.path.exists(path):
      errors = True
    else:
      self.path = path
    # Language
    interpreter = interpreter.lower()
    if not interpreter in self.interpreters:
      errors = True
    self.interpreter = interpreter
    # Target
    self.target = target
    # Check for errors. Eventually prompt the user to change some values
    if errors:
      if not self.path:
        print _("%s is not a valid directory." %(path))

  def list_and_filter(self, pattern, root_path):
    """
    os.walk doesn't support wildcards, so we use this custom function.
    A typical use will be: [f for f in list_and_filter("*.py", "/home/user")]
    @param str pattern the filter
    @param str root_path the path where to start the research
    """
    for path, dirs, files in os.walk(os.path.abspath(root_path)):
      for filename in fnmatch.filter(files, pattern):
        yield os.path.join(path, filename)
  
  def prepend_shebang(self):
    """
    Effectively iter through the wanted files and prepend shebang
    """
    files_to_change = [fn for fn in self.list_and_filter(
                        self.target, self.path)]
    path = getoutput("which env")
    shebang = "#!%s%s\n\n" %(path, self.interpreter)
    for fn in files_to_change:
      print "%s..." %(fn),
      try:
        newf = open("/tmp/prependshebang", "w")
        f = open(fn, 'r')
        newf.write(shebang)
        for line in f.readlines():
          newf.write(line)
        f.close()
        newf.close()
        shmove("/tmp/prependshebang", fn)
        print "[OK]"
      except IOError, e:
        print "[NO]"
        print "\tERROR DETAILS:"
        print "\t\t%s\n" %(e)
    self.prepend_shebang_status = True


  def status(self):
    """
    True if we there are not errors. False if we have to quit. NOW.
    """
    return (not self.path == None) or self.prepend_shebang_status


def main():
  parser = argparse.ArgumentParser()
  parser.add_argument("-p", "--path",
      help=_("Path where to start the research of files defined by <target>"),
      default=".")
  parser.add_argument("-i", "--interpreter",
        help=_("Currently supported: %s" \
   %(", ".join([l for l in PrependSheBang.interpreters]))),
        required=True)
  parser.add_argument("-t", "--target",
        help=_("What files? Ex.: *.py, *pattern*.c, etc.."),
        required=True)
  args = parser.parse_args()
  prepshebang = PrependSheBang(
                    args.path,
                    args.interpreter,
                    args.target)
  if not prepshebang.status():
    return 4
  prepshebang.prepend_shebang()
  if not prepshebang.status():
    return 2
  return 0


if __name__ == "__main__":
  sys.exit(main())

# tabstop=2:shiftwidth=2:expandtab
