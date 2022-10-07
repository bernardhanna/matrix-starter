# matrix-starter

**NOTE**: replace any instances of **Project_Name** with your projects name

1. You should have project created with [matrixstack](https://github.com/bernardhanna/matrixstack) running. 

2. Go to the theme folder of your matrixstack WordPress instance via Terminal (cd /var/www/**PROJECT_NAME**/content/themes)

3. Clone Matrix Starter Theme - ```git clone https://github.com/bernardhanna/matrix-starter.git```

4. Cd to your new cloned repository ```cd /var/www/PROJECT_NAME/content/themes/matrix-starter```

5. CD to bin folder ```cd bin```

6. If its your first time using the script,  run ```bash matrixtheme.sh``` and follow the instructions to link the file to system level with:

```
sudo ln -s /var/www/PROJECT_NAME/content/themes/matrix-starter/bin/matrixtheme.sh /usr/local/bin/newtheme && sudo chmod +x /usr/local/bin/newtheme && newtheme
```

6. Otherwise run ```newtheme```

7. Follow the on screen instructions

8. When prompted to enter "Project name in lowercase" be sure to enter your current **PROJECT_NAME**, not the name of the new theme - That will be next step

8. Give your Theme a name

9. Let the installer do its thing

10. Cd to your newly created theme ```cd /var/www/PROJECT_NAME/content/themes/NEW_THEME_NAME```

11. Get the dependencides by running ```npm install``` inside the theme folder - You should have NPM installed of course

12. Activate theme - ```cd /var/www/PROJECT_NAME && vendor/wp-cli/wp-cli/bin/wp theme activate matrix-starter```

13. Open the whole project in your code editor, if you use Visual Studio Code that will be: ```code /var/www/PROJECT_NAME/content/themes/NEW_THEME_NAME```

14. Run ```gulp``` and start developing! Please note, contributing to this theme is only possible when gulp is run from theme directory, NOT on project root.
