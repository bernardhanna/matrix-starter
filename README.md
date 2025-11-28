# Matrix Starter

Matrix Starter is a modern and highly customizable WordPress theme that uses ACF Builder and Tailwind CSS to streamline development. Modular by its structure, it includes a robust set of tools and features to help you build custom WordPress themes quickly and efficiently.

## Clone and Install

Follow these steps to clone the repository and set up the project on your local machine. We recommend using **Local by Flywheel** and cloning straight to your theme folder for testing and development.

### Prerequisites

Ensure you have the following installed on your system:

* **PHP** (version 7.4 or higher)
* **Composer** (Dependency Manager for PHP)
* **Node.js** (which includes npm)
* **Git** (Version Control System)
* **WordPress** (Installed locally or on a server)

### Installation Steps

1. **Clone the Repository**

   ```bash
   git clone https://github.com/bernardhanna/matrix-starter.git
   ```

2. **Navigate to the Project Directory**

   ```bash
   cd matrix-starter
   ```

3. **Install PHP Dependencies**

   Make sure Composer is installed: [getcomposer.org](https://getcomposer.org/)

   ```bash
   composer install
   ```

4. **Install JavaScript Dependencies**

   Ensure Node.js and npm are installed: [nodejs.org](https://nodejs.org/)

   ```bash
   npm install
   ```

5. **Create and Configure `.env` File**

   Create a `.env` by copying the example:

   ```bash
   cp .env.example .env
   ```

   Then open `.env` and add your WordPress path (the folder that contains `wp-config.php` / `wp-load.php`).
   If your path contains spaces (e.g., Local by Flywheel), **wrap it in quotes**:

   ```dotenv
   # Path to your WordPress root
   # Example (Local by Flywheel):
   WP_PATH="/Users/yourname/Local Sites/your-site/app/public"
   ```

   > The installer **only reads** `.env`; it does not modify or delete it.

6. **Run Development Server with Watchers**

   ```bash
   npm run dev
   ```

   This will:

   * Watch and process CSS changes
   * Watch and process JS changes
   * Start the Webpack dev server with hot reloading

### Additional Steps

7. **Build the Assets for Production**

   ```bash
   npm run build
   ```

8. **Set Up WordPress**

   * **Place the Theme in WordPress:**

     Copy the `matrix-starter` theme folder to your WordPress installation’s `wp-content/themes/` directory (skip if you already cloned directly into `wp-content/themes`):

     ```bash
     cp -R ./matrix-starter /path-to-your-wordpress/wp-content/themes/
     ```

   * **(Optional) Activate the Theme via WP Admin:**

     Log in to **Appearance → Themes** and activate **Matrix Starter**.

9. **Install & Activate Required Items (Theme + Plugins)**

   This command will:

   * Clone **Matrix Component Importer**
   * Clone **Matrix Sitemap Generator**
   * Attempt to **activate both plugins**
   * Attempt to **activate the theme** (best-effort; the script will not fail if activation isn’t possible)
   
 ```
   cd /scripts
  ```
   ```bash
   npm run flexi:install
   ```

   When complete, you can visit the Matrix Components importer UI:

   ```
   /wp-admin/admin.php?page=matrix-ci-admin-page
   ```

### Troubleshooting

* **Local by Flywheel tips:**

  * Make sure the site is **running** in Local.
  * For smoothest results, open **Local → Open Site Shell** and run:

    ```bash
    npm run flexi:install
    ```
* **Activation skipped or failed:**

  * Double-check `WP_PATH` in `.env` (and make sure it’s **quoted** if it includes spaces).
  * You can manually activate via WP-CLI:

    ```bash
    wp --path="$WP_PATH" plugin activate matrix-component-importer matrix-sitemap-generator --skip-plugins --skip-themes
    wp --path="$WP_PATH" theme activate matrix-starter --skip-plugins --skip-themes
    ```
* **Composer Not Found:**

  * Install and add to PATH: [getcomposer.org](https://getcomposer.org/)
* **npm Errors:**

  * Check versions:

    ```bash
    node -v
    npm -v
    ```
* **Permission Issues:**

  * Consider using [nvm](https://github.com/nvm-sh/nvm) or adjusting file permissions.

---

### Features

* **ACF Builder** for custom fields in code
* **Tailwind CSS** for utility-first styling
* **Alpine.js** for lightweight interactivity
* **TypeScript** for better code quality
* **Webpack** for asset bundling
* **Extended CPTs** to quickly create post types & taxonomies
* **log1x/navi** for powerful navigation management
* **log1x/modern-acf-options** for modern ACF options pages
* **log1x/modern-login** for a modern WordPress login screen

### Getting Started

(Your quick-start docs or examples can go here.)

### Contact

Bernard Hanna — [bernard@matrixinternet.ie](mailto:bernard@matrixinternet.ie)
Project Link: [https://github.com/bernardhanna/matrix-starter](https://github.com/bernardhanna/matrix-starter)

## Acknowledgements

* [ACF Builder](https://www.advancedcustomfields.com/)
* [Tailwind CSS](https://tailwindcss.com/)
* [Alpine.js](https://alpinejs.dev/)
* [TypeScript](https://www.typescriptlang.org/)
* [WordPress](https://wordpress.org/)
