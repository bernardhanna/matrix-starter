#!/bin/bash
# A script for moving all dev files back to the theme
txtbold=$(tput bold)
boldyellow=${txtbold}$(tput setaf 3)
boldgreen=${txtbold}$(tput setaf 2)
boldwhite=${txtbold}$(tput setaf 7)
yellow=$(tput setaf 3)
green=$(tput setaf 2)
white=$(tput setaf 7)
txtreset=$(tput sgr0)

mkdir -p $HOME/matrix-tmp
cp $HOME/matrix-tmp/.gitkeep /var/www/matrixdev/content/themes/matrix-starter/sass/components/
cp $HOME/matrix-tmp/.gitkeep /var/www/matrixdev/content/themes/matrix-starter/sass/modules/
mv $HOME/matrix-tmp/.hintrc /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/.stylelintignore /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/.nvmrc /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/.eslintrc.js /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/.browserslistrc /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/.vscode /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/.svgo.yml /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/.accessibilityrc /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/.git /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/.gitignore /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/.jshintignore /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/.travis.yml /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/package.json /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/package-lock.json /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/phpcs.xml /var/www/matrixdev/content/themes/matrix-starter/
sudo mv $HOME/matrix-tmp/node_modules /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/gulpfile.js /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/bin /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/content /var/www/matrixdev/content/themes/matrix-starter/content
mv $HOME/matrix-tmp/.scss-lint.yml /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/front-page.php /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/README.md /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/.stylelintrc /var/www/matrixdev/content/themes/matrix-starter/
mv $HOME/matrix-tmp/.editorconfig /var/www/matrixdev/content/themes/matrix-starter/

# Move the original starter screenshot back in, related to: https://themes.trac.wordpress.org/ticket/100180#comment:2
rm /var/www/matrixdev/content/themes/matrix-starter/screenshot.png
mv $HOME/matrix-tmp/screenshot.png /var/www/matrixdev/content/themes/matrix-starter/

# Restore repository state before move
cd /var/www/matrixdev/content/themes/matrix-starter/ && git stash
git status

echo "
${boldgreen}Matrix starter files moved in and github repository restored, now just do the following:${TXTRESET}"
echo "
1. Upload: https://wordpress.org/themes/upload/
2. Create new release: https://github.com/bernardhanna/matrix-starter/releases
3. Update version to https://matrixwptheme.com
4. All done!
${TXTRESET} "
