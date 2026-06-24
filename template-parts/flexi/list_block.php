<?php
$listHeading = get_sub_field('list_heading');
    $headingLevel = get_sub_field('heading_level') ?: 'h2'; // Default to h2 if not set
    $listItems = get_sub_field('list_items');
    $paddingTop = get_sub_field('padding_top');
    $paddingBottom = get_sub_field('padding_bottom');
    $paddingStyles = '';
    if (!is_null($paddingTop)) {
        $paddingStyles .= "padding-top: {$paddingTop}; ";
    }
    if (!is_null($paddingBottom)) {
        $paddingStyles .= "padding-bottom: {$paddingBottom}; ";
    }
?>
<?php if ($listHeading || $listItems) : ?>
<section class="list-flexi w-full mx-auto px-2 laptop:px-4 flex justify-center" style="<?php echo $paddingStyles; ?>">
    <div class="lg:rounded-md-40 lg:border lg:border-black bg-white lg:shadow-lg max-w-[1368px] p-4 lg:p-10 boxshadow-four">
        <?php if ($listHeading) : ?>
<<?php echo $headingLevel; ?> class="text-mob-xxl-font text-black-full font-reg420 mb-6"><?php echo $listHeading; ?></<?php echo $headingLevel; ?>>
        <?php endif; ?>
<?php if ($listItems) : ?>
<ul class="list-disc pl-8 lg:pl-6">
            <?php foreach ($listItems as $item) : ?>
<li class="text-reg-font lg:text-sm-md-font font-reg420 mb-4 not:last-child:mb-4 lg:mb-10 lg:not:last-child:mb-10">
                <?php echo $item['list_item']; ?>
                <?php if ($item['list_item_content']) : ?>
<span class="text-black-secondary text-mob-md-font font-laca font-light"><?php echo $item['list_item_content']; ?></span>
                <?php endif; ?>
</li>
            <?php endforeach; ?>
</ul>
        <?php endif; ?>
</div>
</section>
<?php endif; ?>
