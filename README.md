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
