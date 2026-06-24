# Featured donuts slider (matrix-starter)

PHP port of the legacy Sage view `old-site/resources/views/home/featuredslider.blade.php`. Renders the black "featured donuts" fade carousel on the homepage (`#featured-section`), with a vertical thumbnail nav and up/down arrows on desktop.

## Files

| Path | Role |
|------|------|
| `template-parts/home/featuredslider.php` | Markup: main fade `#featured-slider`, thumb nav `#donut-thumb-slider`, per-slide content + scoped mobile CSS/JS |
| `template-parts/home/partials/featured-slider-arrows.php` | Desktop-only vertical prev/next SVG arrows |
| `assets/js/rolling-donut-home.js` | `initFeaturedSlider()` — Splide init, thumb sync, slide counter, image animation |
| `inc/rolling-donut-home.php` | Loads home sections + enqueues Splide, legacy CSS, home JS |
| `assets/css/rolling-donut-legacy.css` | Legacy slide / allergen / arrow styling |

The section is printed by `matrix_rd_load_home_sections()` in `inc/rolling-donut-home.php`, which loops the front-page sections (`hero`, `services`, `featuredslider`, ...) and `get_template_part()`s each. It only runs on `is_front_page()`.

## Data model (ACF)

The slider is driven entirely by ACF. The field **names** below are the contract the template reads — keep them identical.

> **Where these fields are registered:** the per-donut fields live in a field group named **"Woo Product"** (with **Donut Fields** + **Box Fields** tabs), now registered in code at `acf-fields/partials/post-types/product.php` and auto-loaded by `inc/autoload-acf-groups.php` on `acf/init`. It is a port of the legacy `App\Fields\Products` + `App\Fields\Partials\Product` classes (the group used to live **only in the database** on the old Sage site, so a fresh database/new build made the slider read empty even though the template code was intact). Because it is now version-controlled, the donut + box fields are present on every `product` post out of the box — no manual ACF import needed. Field **names** are the contract; do not rename them.

### 1. Homepage: which donuts are featured

Legacy source: `old-site/app/Fields/Partials/FeaturedDonuts.php` (added to the `homePage` group under the **Featured Donuts** tab, location `page_type == front_page`).

| Field name | Type | Notes |
|------------|------|-------|
| `donuts` | Post Object | `post_type: product`, **multiple**, `return_format: object` |

The template reads it with `get_field('donuts')`. The order chosen here is the slide order, and the count feeds the `1/6` slide counter.

### 2. Per donut (the `product` post)

Legacy source: `old-site/app/Fields/Partials/ProductDonut.php` / `DonutFields.php` (a **Donut Fields** tab on the `product` post type).

| Field name | Type | Notes |
|------------|------|-------|
| `featured_donut_bg_color` | Color Picker | Slide + right-column background. Falls back to `#ffed56` if empty |
| `thumb_image` | Image (`return_format: url`) | Round nav thumbnail. Falls back to the product's `thumbnail` featured image |
| `product_allergens` | Post Object | `post_type: allergen`, **multiple**, `return_format: object` |

Also pulled from the donut `product` post itself:

- **Title** → `post_title` (slide heading + image alt)
- **Description** → `post_content` run through `apply_filters('the_content', ...)`
- **Main image** → featured image (`get_the_post_thumbnail_url($id, 'large')`, falls back to default size)

### 3. Allergens (the `allergen` post type)

Each allergen is its own post. The template renders one `<li>` per linked allergen using:

- **Icon** → the allergen's featured image (`get_the_post_thumbnail_url($allergen->ID)`)
- **Label** → the allergen's title (`get_the_title($allergen->ID)`)

## Setup checklist (from scratch)

1. **Create allergen posts** (Milk, Eggs, Soybeans, Nuts, Cereals, ...). Set each one's **featured image** to its icon (legacy icons live under `/content/uploads/2023/08/`).
2. **Create / edit each donut** as a `product`. On the product:
   - Set the **featured image** (the large photo shown on the slide).
   - Write the **description** in the main content editor.
   - In **Donut Fields**: pick `featured_donut_bg_color`, upload `thumb_image` (round nav dot), and select the relevant `product_allergens`.
3. **Edit the homepage** (the static front page) → **Featured Donuts** tab → add donuts to the `donuts` field in the order you want them to appear.
4. Load the homepage — the slider renders automatically. No code changes needed to add/remove/reorder donuts.

## How it renders

```24:35:wp-content/themes/matrix-starter/template-parts/home/featuredslider.php
<section class="featured-donuts relative bg-black" id="featured-section">
  <div class="splide relative overflow-visible max-lg:[&_.splide__pagination]:hidden" id="featured-slider">
    <div class="splide__track" id="featured-slider-track">
      <div class="splide__list">
        <?php foreach ($donut_posts as $donut) : ?>
```

- Each donut → one `.splide__slide` in `#featured-slider`.
- Each donut → one round `.donut-indicator` thumbnail in `#donut-thumb-slider`.
- If `donuts` is empty (or none resolve to real posts), the section returns early and renders nothing.

## Splide behaviour (`rolling-donut-home.js`)

`initFeaturedSlider()` runs on `DOMContentLoaded` (after checking `Splide` is defined):

- **Main slider** `#featured-slider`: `type: 'fade'`, `perPage: 1`, arrows + pagination on, `autoplay: true`, `interval: 6000`, `speed: 800`, pause on hover/focus.
- **Thumb nav** `#donut-thumb-slider`: `isNavigation: true`, `focus: 'center'`, no pagination/arrows, `drag: false`, then `featuredSplide.sync(thumbSplide)` so clicking a dot jumps slides.
- **Slide counter**: on every `moved`, `updateSlideCounts(index + 1)` rewrites the `.start-count` span inside each `.slide-count` (the `2/6` text).
- **Image animation**: arrow clicks / slide moves add `animate-up` / `animate-down` to `.featured-image` (desktop ≥ 1200px only).
- The auto-play extension (`window.splide.Extensions`) is mounted when present.

## Assets & enqueue order

`matrix_rd_home_enqueue_assets()` (priority 30) loads on the front page (and About Us):

- Splide CSS/JS `@4.1.4` + auto-play extension `@0.5.3` (jsDelivr CDN).
- `rolling-donut-legacy.css` (versioned via `theme_css_version`).
- `rolling-donut-home.js` (deps: `splide`, `splide-autoplay`, `jquery`, `alpine`).

Two later hooks fix CSS cascade:

- `matrix_rd_home_enqueue_tailwind_after_legacy()` (priority 100) re-queues the Tailwind bundle **after** legacy CSS so utility classes win.
- `matrix_rd_home_featured_slider_overrides()` (priority 110) injects inline CSS to pin the desktop arrows (bottom-right, vertical) and hide them below 992px.

## Responsive notes

- **Desktop (≥ 993px):** vertical thumbnail rail + up/down arrows on the right; full `laptop:h-[800px]` slide height.
- **Mobile / tablet (≤ 1084px):** scoped inline CSS + JS in `featuredslider.php` force the fade track visible and **hide** `#donut-thumb-slider` (the fade transforms otherwise leave slides invisible). These rules are scoped to `#featured-slider` so they don't affect other Splide instances.

## Build

After editing Tailwind classes or `tailwind.config.js`, rebuild theme CSS:

```bash
cd wp-content/themes/matrix-starter && npm run build
```

`rolling-donut-home.js` and the inline arrow CSS are plain assets — no build step, just a hard refresh.

## Gotchas

- **Field names are the contract.** `donuts`, `featured_donut_bg_color`, `thumb_image`, `product_allergens` must match exactly or fields read empty.
- **Allergen icons come from the allergen's featured image**, not an ACF field. A missing featured image = a broken `<img>`.
- **Empty `donuts` = no section.** The template bails early rather than printing an empty carousel.
- **Order is set on the homepage**, not on the products — reordering the `donuts` field reorders the slides.
- Arrows are intentionally **hidden below 992px**; navigation on mobile is via swipe/pagination.
