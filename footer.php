<?php get_template_part('template-parts/footer/newsletter'); ?>
<footer class="w-full bg-secondary">
    <?php get_template_part('template-parts/footer/footer'); ?>

    <?php get_template_part('template-parts/footer/copyright'); ?>

    <?php
    $enable_back_to_top = get_field('back_to_top_settings_enable_back_to_top', 'option');

    if ($enable_back_to_top !== false) : ?>
        <?php get_template_part('template-parts/footer/back-to-top'); ?>
    <?php endif; ?>
</footer>

<?php wp_footer(); ?>


</body>

</html>