# Rolling Donut navbar (matrix-starter)

PHP port of legacy Sage views under `old-site/resources/views/navigation/` and `partials/navigation.blade.php`.

## Files

| Path | Role |
|------|------|
| `template-parts/header/navbar.php` | Shell: Alpine `open` + `showSearch`, `#site-nav` |
| `template-parts/header/navbar/topnav.php` | Phone, account, search, cart |
| `template-parts/header/navbar/logo.php` | Center logos |
| `template-parts/header/navbar/desktop-menu.php` | Left / right / combined desktop lists |
| `template-parts/header/navbar/menu-item.php` | One top-level link + dropdown |
| `template-parts/header/navbar/submenu.php` | Desktop dropdown panel |
| `template-parts/header/navbar/mobile-toggle.php` | Hamburger |
| `template-parts/header/navbar/mobile-utilities.php` | Mobile account + cart |
| `template-parts/header/navbar/mobile-drawer.php` | Full-screen mobile menu |
| `template-parts/header/navbar/search-panel.php` | Alpine live-search overlay (products, pages, posts) |
| `inc/rolling-donut-product-search.php` | REST search API + inline Alpine component (registered via `alpine:init`) + FiboSearch dequeue |
| `inc/rolling-donut-navbar.php` | Navi helpers, cart AJAX, assets |
| `assets/css/rolling-donut-navbar.css` | btn-menu, hamburger, logo, nav-line |

## Menu

Assign **Primary Navigation (Rolling Donut)** (`primary_navigation`) in **Appearance → Menus** (same assignment as legacy).

Fallback: `primary` if `primary_navigation` is empty.

## Theme options (ACF)

Uses legacy option field names from the imported DB:

- `main_logo`, `mobile_logo`, `mobile_logo_open`
- `office_telephone`
- `mobile_menu_bg`

Also editable under **Theme Options → Navbar**.

## Build

After changing `tailwind.config.js`, rebuild theme CSS:

```bash
cd wp-content/themes/matrix-starter && npm run build
```

## Not in this pass

- Sticky duplicate header on scroll (`sections/header.blade.php`)
- Top bar promo (`partials/topbar`)
- Edmondsans / Laca load via `inc/rolling-donut-fonts.php` (CDN Fonts, same URLs as legacy Bedrock theme)
