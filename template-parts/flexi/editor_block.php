<?php
$paddingTop = get_sub_field('padding_top') ?? '0';
    $paddingBottom = get_sub_field('padding_bottom') ?? '0';
?>
<section class="editor-flexi w-full px-4" style="
    padding-top: <?php echo get_sub_field('padding_top') ?? '0'; ?>;
    padding-bottom: <?php echo get_sub_field('padding_bottom') ?? '0'; ?>;
">
    <div class="max-w-max-1364 m-auto e-content">
        <?php echo apply_filters('the_content', (string) get_sub_field('editor_content')); ?>
    </div>
</section>
