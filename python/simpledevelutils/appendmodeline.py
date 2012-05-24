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
import gettext
import argparse
import fnmatch

_ = gettext.gettext

class ChangeVimModelines:
  """ Class to change vim modelines to the files you want. """

  # pairs in the form language: [comment_start_symbol, comment_end_symbol]
  # XXX: PLEASE note that languages'name are lowercase
  languages_and_comments = {
    "python": ["# ", ''],
    "c": ["/* ", " */"]
  }

  path = None
  target = "*.txt"

  default_mode = "tabstop=2:shiftwidth=2:expandtab"
  default_lang = "python"
  
  # the status of the append operation
  append_ml_status = False

  def __init__(self, path, language, modeline, target=None):
    """
    Class contructor.
    @param str path the path where to start the loop
    @param str language what progr. language is in the files to change
    @param str modeline the modeline to append
    @param str target what files to modify? (Ex.: *.py, *.c, *)
    """
    errors = False
    #Path
    if not os.path.exists(path):
      errors = True
    else:
      self.path = path
    # Language
    language = language.lower()
    if not language in self.languages_and_comments.keys():
      errors = True
    self.default_lang = language
    # Modeline
    self.default_mode = modeline
    # Target
    self.target = target
    self.entry_message()
    # Check for errors. Eventually prompt the user to change some values
    if errors:
      if not self.path:
        print _("%s is not a valid directory." %(path))
      else:
        # Select a correct language
        self.change_language()

  def change_language(self, language=None, from_error=False):
    """
    Change the language. If language is None, prompt the user.
    @param str language the language to set or None to ask
    @param bool from_error we HAVE to change the language?
    """
    supported_langs = self.languages_and_comments.keys()
    if from_error:
      print _("Unsupported language. Available languages are:")
      for lang in supported_langs:
        print " - ", lang
    if not language:
      language = raw_input(_("Language: "))
    if not language in supported_langs:
      self.change_language(None, True)
    else:
      self.default_lang = language
  
  def entry_message(self):
    """ Print the entry message. """
    print _("APPEND VIM MODELINE TO SOURCE CODE FILES")
    print
    print _("DEFAULT CONFIGURATION:")
    print _(" - MODE:"), self.default_mode
    print _(" - LANGUAGE:"), self.default_lang

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
  
  def append_modeline(self):
    """
    Effectively iter through the wanted files and append the VIm modeline
    """
    files_to_change = [fn for fn in self.list_and_filter(
                        self.target, self.path)]
    comment_start, comment_end = self.languages_and_comments[
                                    self.default_lang]
    modeline = "\n%s%s%s\n" %(comment_start, self.default_mode, comment_end)
    for fn in files_to_change:
      print "%s..." %(fn),
      try:
        f = open(fn, 'a')
        f.write(modeline)
        f.close()
        print "[OK]"
      except IOError, e:
        print "[NO]"
        print "\tERROR DETAILS:"
        print "\t\t%s\n" %(e)
    self.append_ml_status = True


  def status(self):
    """
    True if we there are not errors. False if we have to quit. NOW.
    """
    return (not self.path == None) or self.append_ml_status


def main():
  parser = argparse.ArgumentParser()
  parser.add_argument("-p", "--path",
      help=_("Path where to start the research of files defined by <target>"),
      default=".")
  parser.add_argument("-la", "--language",
        help=_("Currently supported: %s" \
 %(", ".join([l for l in ChangeVimModelines.languages_and_comments.keys()]))),
        required=True)
  parser.add_argument("-m", "--modeline")
  parser.add_argument("-t", "--target",
        help=_("What files? Ex.: *.py, *pattern*.c, etc.."),
        required=True)
  args = parser.parse_args()
  chvimmodeline = ChangeVimModelines(
                    args.path,
                    args.language,
                    args.modeline,
                    args.target)
  if not chvimmodeline.status():
    return 4
  chvimmodeline.append_modeline()
  if not chvimmodeline.status():
    return 2
  return 0

if __name__ == "__main__":
  sys.exit(main())

# tabstop=2:shiftwidth=2:expandtab
