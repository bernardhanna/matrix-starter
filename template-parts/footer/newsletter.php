<?php
/**
 * Footer newsletter band (Klaviyo) — legacy partials/newsletter.blade.php.
 */
if (! function_exists('get_field') || ! matrix_rd_footer_show_newsletter()) {
    return;
}

$title_one = (string) get_field('footer_newsletter_title_one', 'option');
$title_two = (string) get_field('footer_newsletter_title_two', 'option');
$text      = (string) get_field('footer_newsletter_text', 'option');
?>
<section class="relative newsletter bg-black-full">
  <div class="flex relative flex-col justify-around items-center px-4 pt-4 w-full max-w-full bg-black-full lg:justify-between containermax-md: max-lg:py-4 lg:flex-row lg:mx-auto lg:max-w-max-1504 newsletter-layout">
    <div class="flex flex-col w-full rounded-t-lg newsletter-content">
      <div class="flex flex-col justify-start content-start items-start px-1 pt-1 highlighted">
        <?php if ($title_one !== '') : ?>
        <span class="inline-flex pt-3 pr-10 pl-3 bg-white highlighted-first text-black-full text-mob-xl-font lg:text-1lg-font font-reg420 lg:rounded-t-lg"><?php echo esc_html($title_one); ?></span>
        <?php endif; ?>
        <?php if ($title_two !== '') : ?>
        <span class="px-3 pb-2 bg-white highlighted-second text-black-full text-mob-xl-font lg:text-1lg-font font-reg420"><?php echo esc_html($title_two); ?></span>
        <?php endif; ?>
      </div>
      <?php if ($text !== '') : ?>
      <p class="top-[12px] relative newsletter-text text-white text-sm-font font-lighter font-laca leading-none"><?php echo esc_html($text); ?></p>
      <?php endif; ?>
    </div>
    <div class="w-full newsletter-form lg:w-full max-lg:py-4">
      <div class="klaviyo-form-VEZU7S"></div>
      <script async src="https://static.klaviyo.com/onsite/js/klaviyo.js?company_id=XwQPcq"></script>
      <label class="newsletter-consent">
        <input type="checkbox" class="newsletter-consent__check">
        <span class="newsletter-consent__text">By signing up to our newsletter, you agree to our Terms &amp; Conditions &amp; Privacy Policy.</span>
      </label>
    </div>
  </div>
  <?php
  /*
   * The Klaviyo embed renders its own heading + intro copy above the form. We
   * surface that copy on the left (theme ACF) instead, so hide the duplicates.
   * Matched by text (not class) so Klaviyo's post-submit success message — which
   * is a different richtext block — is left untouched. We also relabel the
   * submit button to "Get 10% Off" and gate it behind the opt-in checkbox so the
   * design's consent control actually blocks submission until ticked. Klaviyo
   * loads async, so observe the section until the copy blocks appear and the
   * button is relabelled (or the timeout fires).
   */
  ?>
  <script>
  (function () {
    var section = document.currentScript.closest('section.newsletter');
    if (!section) { return; }
    var needles = ['STAY IN THE (DONUT) LOOP', 'Get sweet updates'];
    var buttonLabel = 'Get 10% Off';
    var consent = section.querySelector('.newsletter-consent__check');
    function hideDuplicates() {
      var blocks = section.querySelectorAll('.klaviyo-form-richtext');
      var hidden = 0;
      blocks.forEach(function (block) {
        var text = (block.textContent || '').trim();
        if (needles.some(function (n) { return text.indexOf(n) !== -1; })) {
          block.style.display = 'none';
          hidden++;
        }
      });
      return hidden >= needles.length;
    }
    function relabelButton() {
      var btn = section.querySelector('.klaviyo-form-button');
      if (!btn) { return false; }
      if ((btn.textContent || '').trim() !== buttonLabel) {
        btn.textContent = buttonLabel;
      }
      return true;
    }
    function syncConsent() {
      var btn = section.querySelector('.klaviyo-form-button');
      if (!btn) { return false; }
      btn.classList.toggle('is-disabled', !!consent && !consent.checked);
      return true;
    }
    function applyAll() {
      return hideDuplicates() && relabelButton() && syncConsent();
    }
    if (consent) { consent.addEventListener('change', syncConsent); }
    if (applyAll()) { return; }
    var observer = new MutationObserver(applyAll);
    observer.observe(section, { childList: true, subtree: true });
    setTimeout(function () { observer.disconnect(); }, 15000);
  })();
  </script>
</section>
