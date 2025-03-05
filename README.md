# Matrix Starter

Matrix Starter is a modern and highly customizable WordPress theme that uses ACF Builder and Tailwind CSS to streamline development. Modular by it structure, It includes a robust set of tools and features to help you build custom WordPress themes quickly and efficiently.

## Clone and Install

Follow these steps to clone the repository and set up the project on your local machine. Recommend using a Local by flywheel and cloning straight to your theme folder for testing and development.

### Prerequisites

Ensure you have the following installed on your system:

- **PHP** (version 7.4 or higher)
- **Composer** (Dependency Manager for PHP)
- **Node.js** (which includes npm)
- **Git** (Version Control System)
- **WordPress** (Installed locally or on a server)

### Installation Steps

1. **Clone the Repository**

   Open your terminal and run the following command to clone the repository:

   ```bash
   git clone https://github.com/bernardhanna/matrix-starter.git
   ```

2. **Navigate to the Project Directory**

   ```bash
   cd matrix-starter
   ```

3. **Install PHP Dependencies**

   Ensure Composer is installed. If not, you can download it from [getcomposer.org](https://getcomposer.org/).

   ```bash
   composer install
   ```

4. **Install JavaScript Dependencies**

   Ensure Node.js and npm are installed. If not, download them from [nodejs.org](https://nodejs.org/).

   ```bash
   npm install
   ```

5. **Create and Configure `.env` File**

  create a `.env` file by copying the example provided:

   ```bash
   cp .env.example .env
   ```

   Then, open the `.env` file and configure the necessary environment variables as per your setup.

6. **Run Development Server with Watchers**

   Start the development server and watch for changes in your assets:

   ```bash
   npm run dev
   ```

   This command runs multiple scripts concurrently:
   
   - Watches and processes CSS changes.
   - Watches and processes JS changes.
   - Starts the Webpack development server with hot reloading.

### Additional Steps

7. **Build the Assets for Production**

   When you're ready to build the project assets for production, run:

   ```bash
   npm run build
   ```

8. **Set Up WordPress**

   - **Place the Theme in WordPress:**

     Copy the `matrix-starter` theme folder to your local WordPress installation's `wp-content/themes/` directory:

     ```bash
     cp -R ./matrix-starter /path-to-your-wordpress/wp-content/themes/
     ```

   - **Activate the Theme:**

     Log in to your WordPress admin dashboard, navigate to **Appearance > Themes**, and activate the **Matrix Starter** theme.

### Summary of Commands

For quick reference, here are the essential commands:

```bash
# Clone the repository
git clone https://github.com/bernardhanna/matrix-starter.git

# Navigate to the project directory
cd matrix-starter

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# (Optional) Create and configure .env file
cp .env.example .env

# Run development server with watchers
npm run dev

# Build assets for production
npm run build

# Place the theme in WordPress
cp -R ./matrix-starter /path-to-your-wordpress/wp-content/themes/

# Activate the theme via WordPress admin dashboard
```

### Troubleshooting

- **Composer Not Found:**

  Ensure Composer is installed and added to your system's PATH. Visit [getcomposer.org](https://getcomposer.org/) for installation instructions.

- **npm Errors:**

  Ensure you have a compatible version of Node.js and npm. Check your versions with:

  ```bash
  node -v
  npm -v
  ```

- **Permission Issues:**

  If you encounter permission issues during installation, consider using a Node version manager like [nvm](https://github.com/nvm-sh/nvm) or adjusting your system's permissions.

### Contributing

If you'd like to contribute to this project, please fork the repository and submit a pull request with your changes. For major changes, please open an issue first to discuss what you would like to change.

---

### Features

- **ACF Builder:** Easily manage custom fields with PHP code.
- **Tailwind CSS:** Utility-first CSS framework for rapid UI development.
- **Alpine.js:** Lightweight JavaScript framework for interactive components.
- **TypeScript:** Strongly typed programming language for better code quality.
- **Webpack:** Module bundler for compiling assets.
- **Extended CPTs:** quickly build post types and taxonomies without having to write the same code again and again. 
- **log1x/navi:** A simple, flexible, and powerful way to manage navigation in WordPress.
- **log1x/modern-acf-options:** A modern approach to managing ACF options pages.
- **log1x/modern-login:** A modern approach to managing the WordPress login screen.

### Getting Started


### Contact

Provide contact information or links for users to reach out with questions or feedback.

## Contact

Bernard Hanna - bernard@matrixinternet.ie

Project Link: [https://github.com/bernardhanna/matrix-starter](https://github.com/bernardhanna/matrix-starter)

## Acknowledgements

- [ACF Builder](https://www.advancedcustomfields.com/)
- [Tailwind CSS](https://tailwindcss.com/)
- [Alpine.js](https://alpinejs.dev/)
- [TypeScript](https://www.typescriptlang.org/)
- [WordPress](https://wordpress.org/)

