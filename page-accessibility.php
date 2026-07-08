<?php
/**
 * Template Name: Accessibility Statement
 *
 * Accessibility statement + our latest automated accessibility scores.
 * Auto-applies to the page with slug `accessibility`, and is also selectable
 * as a page template.
 */

get_header();
?>
<main id="main-content" class="site-main w-full overflow-hidden bg-white">
  <?php get_template_part('template-parts/header/page-header-rd'); ?>
  <div class="w-full px-4 pt-10 pb-16 mx-auto lg:pt-16 max-w-max-1038">
    <?php
    while (have_posts()) :
        the_post();
        ?>
      <div class="gutenburg max-w-max-720 mx-auto text-base-font font-lighter text-black-full">
        <?php
        if (trim(get_the_content()) !== '') {
            the_content();
        } else {
            ?>
            <p><?php esc_html_e('The Rolling Donut is committed to making our website accessible to everyone, including people who use assistive technologies such as screen readers, keyboard navigation, and screen magnifiers.', 'matrix-starter'); ?></p>
            <p><?php esc_html_e('We aim to conform to the Web Content Accessibility Guidelines (WCAG) 2.1 at Level AA. We test our key pages with automated tooling and make ongoing improvements.', 'matrix-starter'); ?></p>
            <h2><?php esc_html_e('What we do', 'matrix-starter'); ?></h2>
            <ul>
              <li><?php esc_html_e('Provide a visible “Skip to content” link and clear keyboard focus styles.', 'matrix-starter'); ?></li>
              <li><?php esc_html_e('Use semantic landmarks (header, navigation, main, footer) and descriptive headings.', 'matrix-starter'); ?></li>
              <li><?php esc_html_e('Give buttons, links, and form fields meaningful, screen-reader-friendly labels.', 'matrix-starter'); ?></li>
              <li><?php esc_html_e('Add text alternatives for meaningful images and hide decorative ones from assistive tech.', 'matrix-starter'); ?></li>
            </ul>
            <h2><?php esc_html_e('Need help or found a problem?', 'matrix-starter'); ?></h2>
            <p>
              <?php
              printf(
                  /* translators: %s: contact page link. */
                  esc_html__('If you experience any difficulty using this website, please %s and we will do our best to help and to fix the issue.', 'matrix-starter'),
                  '<a class="underline hover:no-underline" href="' . esc_url(home_url('/contact-us/')) . '">' . esc_html__('contact us', 'matrix-starter') . '</a>'
              );
              ?>
            </p>
            <?php
        }
        ?>
      </div>
      <?php
    endwhile;

    $scores = function_exists('matrix_rd_a11y_render_scores') ? matrix_rd_a11y_render_scores() : '';
    if ($scores !== '') :
        ?>
      <div class="mt-14 pt-10 border-t border-grey-border">
        <?php echo $scores; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped values in matrix_rd_a11y_render_scores(). ?>
      </div>
      <?php
    endif;
    ?>
  </div>
</main>
<?php
get_footer();
