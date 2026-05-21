<?php
$button_bg_color = get_field('back_to_top_settings_button_bg_color', 'option') ?: '#000';
$button_hover_bg_color = get_field('back_to_top_settings_button_hover_bg_color', 'option') ?: '#0098D8';
?>
<button id="backToTop"
  class="flex fixed right-5 bottom-5 invisible justify-center items-center w-14 h-14 rounded-full opacity-0 transition duration-300">
<svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
<g filter="url(#filter0_d_431_13609)">
<rect x="4" y="2" width="48" height="48" rx="24" fill="white" shape-rendering="crispEdges"/>
<path d="M34 29L28 23L22 29" stroke="#00628F" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</g>
<defs>
<filter id="filter0_d_431_13609" x="0" y="0" width="56" height="56" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
<feFlood flood-opacity="0" result="BackgroundImageFix"/>
<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
<feOffset dy="2"/>
<feGaussianBlur stdDeviation="2"/>
<feComposite in2="hardAlpha" operator="out"/>
<feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.15 0"/>
<feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_431_13609"/>
<feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_431_13609" result="shape"/>
</filter>
</defs>
</svg>
</button>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const backToTop = document.getElementById("backToTop");
    window.addEventListener("scroll", function() {
      if (window.scrollY > 200) {
        backToTop.classList.remove("opacity-0", "invisible");
        backToTop.classList.add("opacity-100", "visible");
      } else {
        backToTop.classList.remove("opacity-100", "visible");
        backToTop.classList.add("opacity-0", "invisible");
      }
    });
    backToTop.addEventListener("click", function() {
      window.scrollTo({
        top: 0,
        behavior: "smooth"
      });
    });
  });
</script>