#!/bin/bash
# @Author: Bernard Hanna
# @Date:   2022-10-07 12:13:10
# @Last Modified time: 2022-10-07 12:41:25
#!/bin/bash

# Script specific vars
SCRIPT_LABEL='for linux ubuntu 20.04'
SCRIPT_VERSION='1.0.0'

# Vars needed for this file to function globally
CURRENTFILE=`basename $0`

# Determine scripts location to get imports right
if [ "$CURRENTFILE" = "matrixtheme.sh" ]; then
  SCRIPTS_LOCATION="$( pwd )"
  source ${SCRIPTS_LOCATION}/tasks/variables.sh
  source ${SCRIPTS_LOCATION}/tasks/header.sh
  exit
else
  DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
  ORIGINAL_FILE=$( readlink $DIR/$CURRENTFILE )
  SCRIPTS_LOCATION=$( dirname $ORIGINAL_FILE )
fi

# Final note about server requirements
echo ""
echo "${WHITE}Using this start script requires you use the following:
https://github.com/bernardhanna/matrixstack
https://github.com/bernardhanna/matrix-starter
https://github.com/bernardhanna/devpackages
${TXTRESET}"

# Import required tasks
source ${SCRIPTS_LOCATION}/tasks/imports.sh

# Replace Matrix-starter with your theme name and other seds
source ${SCRIPTS_LOCATION}/tasks/replaces.sh

# The end
source ${SCRIPTS_LOCATION}/tasks/footer.sh
