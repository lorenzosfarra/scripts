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
import gettext
from shutil import move as shmove
import argparse
import fnmatch

_ = gettext.gettext

class AppendCopyright:
  """
  Class that helps you to easily add copyright's infos to your file/project.
  """
  languages_comments_exts = {
    "python": ["# ", "", "*.py"],
    "c": ["/* ", " */", "*.c"],
    "java": ["/* ", " */", "*.java"]
  }

  licenses = {
    "gpl2": "gpl-2.0.txt",
    "gpl3": "gpl-3.0.txt",
    "lgpl2": "lgpl-2.0.txt",
    "lgpl3": "lgpl-3.0.txt",
    "agpl3": "agpl-3.0.txt"
  }

  path = None
  target = "*.txt"

  license = None
  language = None
  project_name = None
  skiplines = 1

  # the status of the append operation
  append_license_status = False

  def __init__(self, path, language, license, target, 
                project_name=None,skiplines=1):
    """
    Class constructor.
    @param str path the path where to start the loop
    @param str language what progr. language is in the files to change
    @param str license what license?
    @param str target what files to modify? (Ex.: *.py, *.c, *)
    @param str project_name project's name
    @param int skiplines how many lines to skip from the head of the files
    """
    errors = False
    # Path
    if not os.path.exists(path):
      errors = True
    else:
      self.path = path
    # Language
    language = language.lower()
    if not language in self.languages_comments_exts.keys():
      errors = True
    self.language = language
    # Project Name
    if project_name:
      self.project_name = project_name
    # License
    if not license in self.licenses.keys():
      errors = True
    else:
      self.license = license
    # Target
    self.target = target
    self.skiplines = skiplines
    self.entry_message()
    # Check for errors. Eventually prompt the user to change some values
    if errors:
      if not self.path:
        print _("%s is not a valid directory." %(path))
      if not self.license:
        print _("%s is not a valid license." %(license))
        print _("Valid licenses are:")
        for l in self.licenses.keys():
          print " - %s" %l
    else:
      self.append_license_status = True

  def entry_message(self):
    """ Print the entry message. """
    print _("APPEND LICENSE TO SOURCE CODE FILES")
    print
    print _("AVAILABLE LICENSES ARE:")
    for l in self.licenses.keys():
      print " - %s" %l

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

  def append_license(self):
    """Effectively iter through the wanted files and append the license
    header."""
    if os.path.isfile(self.path):
      # Only 1 file
      files_to_change = [self.path]
    else:
      files_to_change = [fn for fn in self.list_and_filter(
                          self.target, self.path)]
      comment_start, comment_end, license_file = self.languages_comments_exts[
                                                            self.language]
      license = None
      license_number = 0
      if "2" in self.license:
        license_number = 2
      elif "3" in self.license:
        license_number = 3
      if self.project_name:
        f = open("licenses/appendlicenseproject.txt", 'r')
      else:
        f = open("licenses/appendlicense.txt", 'r')
      try:
        # Open license file
        if "lgpl" in self.license:
          license = f.read() %{
              "projectname": self.project_name,
              "licensetype": "Lesser",
              "licenseversion": license_number
          }
        elif "agpl" in self.license:
          license = f.read() %{
              "projectname": self.project_name,
              "licensetype": "Affero",
              "licenseversion": license_number
          }
        else:
          license = f.read() %{
              "projectname": self.project_name,
              "licensetype": "",
              "licenseversion": license_number
          }
        f.close()
      except IOError, e:
        print _("Unable to open license file:")
        print e
        return 9
      for fn in files_to_change:
        print "%s..." %(fn),
        try:
          newf = open("/tmp/appendgpllicense", "w")
          oldf = open(fn, "r")
          # one line for the shebang
          # If there is not a "comment_end", we have to prepend the 
          # comment_start to all the lines
          lines = license.split("\n")
          oldlines = oldf.readlines()
          oldf.close()
          iterl = 0
          while iterl < self.skiplines:
            newf.write(oldlines[iterl])
            iterl += 1
          if not comment_end:
            lines = [comment_start + line for line in lines]
          else:
            lines[0] = comment_start + lines[0]
            lines[-1] = lines[-1] + comment_end
          for line in lines:
            newf.write(line + "\n")
          # loop for the original file without 1st line
          for line in oldlines[self.skiplines:]:
            newf.write(line)
          newf.close()
          # time to rename
          shmove("/tmp/appendgpllicense", fn)
          print "[OK]"
        except IOError, e:
          print "[NO]"
          print "\tERROR DETAILS:"
          print "\t\t%s\n" %(e)
      self.append_license_status = True

  def status(self):
    """
    True if we there are not errors. False if we have to quit. NOW.
    """
    return (not self.path == None) and self.append_license_status and \
        (not self.license == None)


def main():
  parser = argparse.ArgumentParser()
  parser.add_argument("-p", "--path",
      help=_("Path where to start the research of files defined by <target>"),
      default=".")
  parser.add_argument("-la", "--language",
        help=_("Currently supported: %s" \
   %(", ".join([l for l in AppendCopyright.languages_comments_exts.keys()]))),
        required=True)
  parser.add_argument("-li", "--license",
        help=_("Currently supported: %s" \
     %(", ".join([l for l in AppendCopyright.licenses.keys()]))),
        required=True)
  parser.add_argument("-t", "--target",
        help=_("What files? Ex.: *.py, *pattern*.c, etc.."),
        required=True)
  parser.add_argument("-pn", "--projectname")
  parser.add_argument("-sl", "--skiplines",
        help=_("How many lines to skip from the head of the files."),
        default=1, type=int)
  args = parser.parse_args()
  appendcopyright = AppendCopyright(
                    args.path,
                    args.language,
                    args.license,
                    args.target,
                    args.projectname,
                    args.skiplines)
  if not appendcopyright.status():
    return 4
  appendcopyright.append_license()
  if not appendcopyright.status():
    return 2
  return 0


if __name__ == "__main__":
  sys.exit(main())

# tabstop=2:shiftwidth=2:expandtab
