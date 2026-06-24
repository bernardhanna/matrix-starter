<?php
/**
 * Instagram "Follow" strip — curated feed shown above the footer.
 * Data: Theme Options → Instagram Feed (inc/theme-options/instagram_feed.php).
 * Figma: Rolling Donut Dev board node 55:94305.
 */

defined('ABSPATH') || exit;

$ig_items = get_field('instagram_follow_items', 'option');

// Nothing curated yet — render nothing rather than an empty strip.
if (empty($ig_items) || !is_array($ig_items)) {
    return;
}

$ig_eyebrow = (string) (get_field('instagram_follow_eyebrow', 'option') ?: 'Follow');
$ig_heading = (string) (get_field('instagram_follow_heading', 'option') ?: 'The Rolling Donut');
$ig_handle  = (string) (get_field('instagram_follow_handle', 'option') ?: 'The Rolling Donut');
$ig_profile = (string) (get_field('instagram_follow_profile_url', 'option') ?: get_field('instagram_profile_url', 'option'));

$ig_instance = 'rd-ig-' . uniqid();
?>
<section class="rd-ig" aria-label="<?php esc_attr_e('Follow The Rolling Donut on Instagram', 'matrix-starter'); ?>" id="<?php echo esc_attr($ig_instance); ?>">
  <div class="rd-ig__inner">
    <div class="rd-ig__head">
      <?php if ($ig_eyebrow !== '') : ?>
        <p class="rd-ig__eyebrow"><?php echo esc_html($ig_eyebrow); ?></p>
      <?php endif; ?>
      <?php if ($ig_heading !== '') : ?>
        <h2 class="rd-ig__heading"><?php echo esc_html($ig_heading); ?></h2>
      <?php endif; ?>
      <?php if ($ig_handle !== '') : ?>
        <?php if ($ig_profile !== '') : ?>
          <a class="rd-ig__handle" href="<?php echo esc_url($ig_profile); ?>" target="_blank" rel="noopener noreferrer">
            <span><?php echo esc_html($ig_handle); ?></span>
            <i class="fab fa-instagram" aria-hidden="true"></i>
          </a>
        <?php else : ?>
          <span class="rd-ig__handle">
            <span><?php echo esc_html($ig_handle); ?></span>
            <i class="fab fa-instagram" aria-hidden="true"></i>
          </span>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <div class="rd-ig__carousel">
      <button type="button" class="rd-ig__nav rd-ig__nav--prev" aria-label="<?php esc_attr_e('Previous', 'matrix-starter'); ?>" hidden>
        <svg viewBox="0 0 48 48" width="48" height="48" aria-hidden="true" focusable="false">
          <circle cx="24" cy="24" r="23" fill="#000" />
          <path d="M27 16l-8 8 8 8" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </button>

      <ul class="rd-ig__track" role="list">
        <?php foreach ($ig_items as $item) :
            $image_id = is_array($item) && isset($item['image']) ? $item['image'] : 0;
            if (!$image_id) {
                continue;
            }
            $link    = is_array($item) && !empty($item['link']) ? $item['link'] : $ig_profile;
            $caption = is_array($item) && !empty($item['caption']) ? $item['caption'] : '';
            $alt     = $caption !== '' ? $caption : sprintf(__('%s on Instagram', 'matrix-starter'), $ig_handle);

            $img = wp_get_attachment_image(
                $image_id,
                'medium_large',
                false,
                [
                    'class'   => 'rd-ig__img',
                    'alt'     => esc_attr($alt),
                    'loading' => 'lazy',
                ]
            );
            if (!$img) {
                continue;
            }
            ?>
          <li class="rd-ig__item">
            <?php if ($link !== '') : ?>
              <a class="rd-ig__card" href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener noreferrer">
                <?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
              </a>
            <?php else : ?>
              <span class="rd-ig__card"><?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>

      <button type="button" class="rd-ig__nav rd-ig__nav--next" aria-label="<?php esc_attr_e('Next', 'matrix-starter'); ?>">
        <svg viewBox="0 0 48 48" width="48" height="48" aria-hidden="true" focusable="false">
          <circle cx="24" cy="24" r="23" fill="#000" />
          <path d="M21 16l8 8-8 8" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </button>
    </div>
  </div>
</section>

<style>
  .rd-ig {
    width: 100%;
    padding: 40px 0 56px;
  }
  .rd-ig__inner {
    width: 100%;
    max-width: 1607px; /* 1575px content + 16px gutters */
    margin: 0 auto;
    padding: 0 16px;
  }
  .rd-ig__head {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 32px;
  }
  .rd-ig__eyebrow {
    margin: 0;
    font-size: 18px;
    font-weight: 500;
    line-height: 1.2;
    color: #000;
  }
  .rd-ig__heading {
    margin: 0;
    font-size: clamp(26px, 4vw, 36px);
    font-weight: 700;
    line-height: 1.1;
    color: #000;
  }
  .rd-ig__handle {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 18px;
    color: #252525;
    text-decoration: none;
    width: fit-content;
  }
  .rd-ig__handle:hover span { text-decoration: underline; }
  .rd-ig__handle i { font-size: 24px; line-height: 1; }

  .rd-ig__carousel { position: relative; }

  .rd-ig__track {
    list-style: none;
    margin: 0;
    padding: 4px; /* room for the 4px border so it isn't clipped on edges */
    display: flex;
    gap: 16px;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
  }
  .rd-ig__track::-webkit-scrollbar { display: none; }

  .rd-ig__item {
    flex: 0 0 auto;
    scroll-snap-align: start;
  }
  .rd-ig__card {
    display: block;
    width: 300px;
    height: 285px;
    max-width: 78vw;
    border: 4px solid #000;
    border-radius: 10px;
    overflow: hidden;
    background: #f2f2f2;
  }
  .rd-ig__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.4s ease;
  }
  .rd-ig__card:hover .rd-ig__img { transform: scale(1.04); }

  .rd-ig__nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 2;
    width: 48px;
    height: 48px;
    padding: 0;
    border: 0;
    background: transparent;
    cursor: pointer;
    border-radius: 50%;
    line-height: 0;
    filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.25));
  }
  .rd-ig__nav[hidden] { display: none; }
  .rd-ig__nav svg { display: block; }
  .rd-ig__nav--prev { left: -11px; }
  .rd-ig__nav--next { right: -11px; }
  .rd-ig__nav:focus-visible { outline: 2px solid #000; outline-offset: 2px; }

  @media (max-width: 767px) {
    .rd-ig { padding: 28px 0 40px; }
    .rd-ig__head { margin-bottom: 20px; }
    .rd-ig__nav--prev { left: 4px; }
    .rd-ig__nav--next { right: 4px; }
  }
</style>

<script>
  (function () {
    var root = document.getElementById('<?php echo esc_js($ig_instance); ?>');
    if (!root) return;
    var track = root.querySelector('.rd-ig__track');
    var prev = root.querySelector('.rd-ig__nav--prev');
    var next = root.querySelector('.rd-ig__nav--next');
    if (!track) return;

    function step() {
      var card = track.querySelector('.rd-ig__item');
      if (!card) return track.clientWidth * 0.8;
      var styles = window.getComputedStyle(track);
      var gap = parseInt(styles.columnGap || styles.gap || '16', 10) || 16;
      return card.getBoundingClientRect().width + gap;
    }

    function update() {
      var tolerance = 8; // track has 4px padding so resting scrollLeft can be ~4
      var maxScroll = track.scrollWidth - track.clientWidth - tolerance;
      var overflows = track.scrollWidth - track.clientWidth > tolerance;
      if (prev) prev.hidden = track.scrollLeft <= tolerance;
      if (next) next.hidden = !overflows || track.scrollLeft >= maxScroll;
    }

    if (prev) prev.addEventListener('click', function () { track.scrollBy({ left: -step(), behavior: 'smooth' }); });
    if (next) next.addEventListener('click', function () { track.scrollBy({ left: step(), behavior: 'smooth' }); });
    track.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    update();
  })();
</script>
