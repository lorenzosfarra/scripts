#!/bin/bash
# This file is part of Linode Quota.
# Author: Lorenzo Sfarra <lorenzosfarra@ubuntu.com>
# 
# Linode Quota is free software: you can redistribute it and/or modify
# it under the terms of the GNU  General Public License as published by
# the Free Software Foundation, either version  2f the License, or
# (at your option) any later version.
# 
# Linode Quota is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with Linode Quota.  If not, see <http://www.gnu.org/licenses/>.
# 
# 

USERNAME="Your_Linode_Username"
PASSWORD="Your_Linode_Password"

# GET AND DISPLAY THE TRAFFIC QUOTA USED!!
function get_trafficquota() {
  POSTDATA="auth_username=$USERNAME&auth_password=$PASSWORD"
  LOGINURL="https://manager.linode.com/session/login"

  wget -q -O /dev/null --cookies=on --keep-session-cookies --save-cookies=/tmp/cookie --post-data=$POSTDATA $LOGINURL

  wget -q -O out.html --load-cookies=/tmp/cookie "https://manager.linode.com/linodes"

  OUTP=`/bin/grep Quota out.html`
  rm out.html
  echo "---------------------------------------------------"
  echo "            USED TRAFFIC QUOTA IS:"
  echo $OUTP
  echo "---------------------------------------------------"
}

get_trafficquota
