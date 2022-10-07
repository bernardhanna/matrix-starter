#!/bin/bash
# A script for moving all dev files out of the theme for testing with Theme Check plugin
txtbold=$(tput bold)
boldyellow=${txtbold}$(tput setaf 3)
boldgreen=${txtbold}$(tput setaf 2)
boldwhite=${txtbold}$(tput setaf 7)
yellow=$(tput setaf 3)
green=$(tput setaf 2)
white=$(tput setaf 7)
txtreset=$(tput sgr0)

echo "${YELLOW}Moving dev files out...${TXTRESET}"
mkdir -p $HOME/matrix-tmp
find . -name '.DS_Store' -type f -delete
find ../ -name '.DS_Store' -type f -delete
rm /var/www/matrixdev/content/themes/matrix-starter/sass/components/.gitkeep $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/sass/modules/.gitkeep $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/.hintrc $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/.stylelintignore $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/.nvmrc $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/.eslintrc.js $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/.browserslistrc $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/.vscode $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/.svgo.yml $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/.accessibilityrc $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/.git $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/.gitignore $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/.jshintignore $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/.travis.yml $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/package.json $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/package-lock.json $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/phpcs.xml $HOME/matrix-tmp/
sudo mv /var/www/matrixdev/content/themes/matrix-starter/node_modules $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/gulpfile.js $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/bin $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/content $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/__MACOSX $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/.scss-lint.yml $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/front-page.php $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/README.md $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/.stylelintrc $HOME/matrix-tmp/
mv /var/www/matrixdev/content/themes/matrix-starter/.editorconfig $HOME/matrix-tmp/
mkdir -p $HOME/matrix-tmp/template-parts
mkdir -p $HOME/matrix-tmp/template-parts/header
mkdir -p $HOME/matrix-tmp/template-parts/footer

# Remove custom stuff that are not part of an official WordPress theme in WP theme directory,
# Because:
# REQUIRED: The theme uses the register_taxonomy() function, which is plugin-territory functionality.
# REQUIRED: The theme uses the register_post_type() function, which is plugin-territory functionality.
rm /var/www/matrixdev/content/themes/matrix-starter/inc/includes/taxonomy.php
rm /var/www/matrixdev/content/themes/matrix-starter/inc/includes/post-type.php

# Screenshot, related to: https://themes.trac.wordpress.org/ticket/100180#comment:2
mv /var/www/matrixdev/content/themes/matrix-starter/screenshot.png $HOME/matrix-tmp/
cd /var/www/matrixdev/content/themes/matrix-starter/
wget https://i.imgur.com/idVvQKv.png
mv -v idVvQKv.png screenshot.png

# Moving to bin dir
cd $HOME/matrix-tmp/bin

echo "
${boldgreen}Done! Next steps:${TXTRESET}"
echo "
${boldwhite}1. Click the Check it -button: http://matrixdev.test/wp/wp-admin/themes.php?page=themecheck
2. Run: sh matrix-pack.sh (this also runs matrix-move-in.sh)
3. Follow instructions
${TXTRESET} "
