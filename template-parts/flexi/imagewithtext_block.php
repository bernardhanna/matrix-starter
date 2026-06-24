<?php
$reverseLayout = get_sub_field('reverse_layout_imgtxt');
    $flexDirectionClass = $reverseLayout ? 'flex-col-reverse md:flex-row-reverse' : 'flex-col-reverse md:flex-row';
    $contentJustification = get_sub_field('content_justification_imgtxt') ?: 'justify-center';
    $eventImage = get_sub_field('event_image_imgtxt');
    $eventHeading = get_sub_field('event_heading_imgtxt');
    $eventText = get_sub_field('event_text_imgtxt');
    $eventButton = get_sub_field('event_button_imgtxt');
    $imageDimensions = get_sub_field('image_dimensions');
    $event_heading_tag = get_sub_field('event_heading_tag');
    $imageBorder = get_sub_field('image_border');
    $borderClass = $imageBorder ? 'border-4 border-black-full shadow-small lg:border-none lg:shadow-none' : '';
    $imageStyle = '';
?>
<section class="event-flexi">
    <div class="flex <?php echo $flexDirectionClass; ?> max-w-max-1364 mx-auto px-4">
        <div class="text-left md:w-1/2 lg:p-4">
            <?php if ($eventImage) : ?>
<img class="rounded-20px object-cover border-2 border-solid border-black-full <?php echo $borderClass; ?> w-auto mx-auto <?php echo $imageStyle; ?>"
                    src="<?php echo $eventImage['url']; ?>" alt="<?php echo $eventImage['alt']; ?>">
            <?php endif; ?>
</div>
        <div class="<?php echo $contentJustification; ?> w-full content md:w-1/2 lg:flex lg:flex-col p-4 lg:pl-8 xxl:pr-37">
            <<?php echo $event_heading_tag; ?> class="pb-5 text-lg-font lg:text-xl-font font-reg420 leading-3xl">
                <?php echo $eventHeading; ?></<?php echo $event_heading_tag; ?>>
                <div class="w-full leading-none text-reg-font lg:text-mob-md-font text-black-font">
                    <?php echo $eventText; ?></div>
                <?php if ($eventButton) : ?>
<a href="<?php echo $eventButton['url']; ?>"
                        class="btn mb-8 md:mb-0 mt-8 md:mt-16 w-full h-[56px] max-w-[318px] text-white text-sm-md-font lg:mob-lg-font font-420 bg-black-full border-radius-large py-4 font-reg420 hover:bg-yellow-primary hover:text-black-full lg:w-full"><?php echo $eventButton['title']; ?></a>
                <?php endif; ?>
</div>
    </div>
</section>
