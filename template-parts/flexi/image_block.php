<?php
/**
 * Legacy flexi layout (ported from Blade).
 */
?>
<section class="image-flexi w-full px-4">
    <div class="max-w-max-1364 m-auto">
        <?php
$image = get_sub_field('image_content');
            $srcset = wp_get_attachment_image_srcset($image['ID']);
            $sizes = '(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 25vw';
        ?>
<?php if ($image) : ?>
<img class="w-full rounded-img object-cover h-auto lg:min-h-[408px]"
             src="<?php echo $image['url']; ?>"
             alt="<?php echo $image['alt'] ?? 'Default image description'; ?>"
             srcset="<?php echo $srcset; ?>"
             sizes="<?php echo $sizes; ?>">
        <?php endif; ?>
</div>
</section>
