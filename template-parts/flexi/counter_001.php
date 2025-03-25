<?php

/**
 * @Author: Bernard Hanna
 * @Date:   2025-03-06 17:13:41
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2025-03-21 10:35:05
 */
?>

<section class="relative flex overflow-hidden">
  <div class="flex flex-col items-center w-full pt-5 pb-5 mx-auto max-w-container max-lg:px-5">
    <div class="flex flex-col items-center justify-center px-16 py-32 overflow-hidden bg-white max-md:px-5 max-md:py-24">
      <div class="w-full max-w-screen-lg max-md:max-w-full">
        <div class="flex gap-5 max-md:flex-col">
          <div class="w-[56%] max-md:ml-0 max-md:w-full">
            <div class="flex flex-col grow max-md:mt-10 max-md:max-w-full">
              <h2 class="self-start text-5xl font-bold leading-none text-zinc-800 max-md:text-4xl">
                Our Statistic
              </h2>
              <p class="mt-6 text-base leading-6 text-stone-500 max-md:max-w-full">
                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's
                standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a
                type specimen book.
              </p>
            </div>
          </div>
          <div class="ml-5 w-[44%] max-md:ml-0 max-md:w-full">
            <div class="mt-8 grow max-md:mt-10">
              <div class="flex gap-5 max-md:flex-col">
                <!-- Counter 1 -->
                <article class="w-6/12 max-md:ml-0 max-md:w-full">
                  <div
                    x-data="{ count: 0, target: 350 }"
                    x-init="let interval = setInterval(() => { 
                      if (count < target) count += 5;
                      else { count = target; clearInterval(interval); } 
                    }, 20);"
                    class="flex flex-col items-center w-full px-10 font-bold text-center rounded-lg grow py-7 bg-indigo-50 max-md:px-5 max-md:mt-4">
                    <span class="text-5xl leading-none text-zinc-800 max-md:text-4xl" x-text="count" aria-label="350 Happy Clients"></span>
                    <span class="mt-2 text-base text-neutral-400">Happy Client</span>
                  </div>
                </article>

                <!-- Counter 2 -->
                <article class="w-6/12 ml-5 max-md:ml-0 max-md:w-full">
                  <div
                    x-data="{ count: 0, target: 325 }"
                    x-init="let interval = setInterval(() => { 
                      if (count < target) count += 5;
                      else { count = target; clearInterval(interval); } 
                    }, 20);"
                    class="flex flex-col items-center w-full px-10 font-bold text-center bg-white grow py-7 max-md:px-5 max-md:mt-4">
                    <span class="text-5xl leading-none text-zinc-800 max-md:text-4xl" x-text="count" aria-label="325 Projects Done"></span>
                    <span class="mt-2 text-base text-neutral-400">Project Done</span>
                  </div>
                </article>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<section class="relative flex py-10 overflow-hidden bg-zinc-100">
  <div class="flex flex-col items-center w-full pt-5 pb-5 mx-auto">
    <div class="w-full overflow-hidden" aria-label="Our Services">
      <ul class="flex items-center gap-32 w-max animate-scroll300" role="list">
        <li class="text-3xl font-bold leading-10 text-left uppercase">Brand Strategy</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Digital studio</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Email Marketing</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Data Analytics</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Innovative Ideas</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Ui/ux Design</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Brand Strategy</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Digital studio</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Email Marketing</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Data Analytics</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Innovative Ideas</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Ui/ux Design</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Brand Strategy</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Digital studio</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Email Marketing</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Data Analytics</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Innovative Ideas</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Ui/ux Design</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Brand Strategy</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Digital studio</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Email Marketing</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Data Analytics</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Innovative Ideas</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Ui/ux Design</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Brand Strategy</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Digital studio</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Email Marketing</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Data Analytics</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Innovative Ideas</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Ui/ux Design</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Brand Strategy</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Digital studio</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Email Marketing</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Data Analytics</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Innovative Ideas</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Ui/ux Design</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Brand Strategy</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Digital studio</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Email Marketing</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Data Analytics</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Innovative Ideas</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Ui/ux Design</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Brand Strategy</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Digital studio</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Email Marketing</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Data Analytics</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Innovative Ideas</li>
        <li class="text-3xl font-bold leading-10 text-left uppercase">Ui/ux Design</li>
      </ul>
    </div>
  </div>
</section>

<section class="relative flex pb-40 overflow-hidden pt-36 bg-teal-50 max-md:pt-24 max-md:pb-28">
  <div class="flex flex-col items-center w-full pt-5 pb-5 mx-auto max-w-container max-lg:px-5">
    <div class="mb-10 text-center max-sm:mb-5">
      <h2 class="mb-4 text-5xl font-extrabold text-center text-gray-800 leading-[50px] max-md:mb-2.5 max-md:text-3xl max-md:leading-10">
        What I'm Offer
      </h2>
      <p class="mx-auto mt-auto text-xl leading-8 text-center max-w-[500px] max-md:text-lg max-md:leading-7 max-md:max-w-[430px] max-sm:text-base max-sm:leading-7">
        Lorem Ipsum is simply dummy text of the printing and typesetting industry
      </p>
    </div>
    <div class="grid grid-cols-3 gap-4 max-lg:grid-cols-1">
      <div class="p-4">
        <div class="p-8 bg-white rounded-3xl duration-[0.4s] ease-[cubic-bezier(0.25,1,0.5,1)] shadow-[rgba(43,171,160,0.14)_0px_1px_2px_1px] max-md:p-11">
          <div class="inline-flex justify-center items-center mb-8 h-[90px] rounded-[100%] w-[90px]">
            <img src="https://html.merku.love/talking-minds/assets/images/icons/icon_brain.svg" alt="Brain Icon - Talking Minds – Psychotherapist Site Template" class="max-w-full align-middle select-none overflow-x-clip overflow-y-clip">
          </div>
          <div>
            <h3 class="mb-5 text-2xl font-bold leading-8 text-gray-800 max-md:mb-3 max-md:text-2xl max-md:leading-7">
              Depression Therapy
            </h3>
            <p class="mb-8 max-md:mb-5">
              Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusant doloremqe laudantium.
            </p>
            <a href="https://html.merku.love/talking-minds/service_details.html" class="inline-flex relative items-center font-bold leading-5 text-teal-500 cursor-pointer duration-[0.4s] ease-[cubic-bezier(0.25,1,0.5,1)] max-md:leading-4 w-fit whitespace-nowrap hover:bg-hover hover:text-hover">
              <span class="relative font-bold leading-5 text-teal-500 cursor-pointer max-md:leading-4">
                More Info
              </span>
              <span class="flex justify-center items-center ml-2 text-base font-bold leading-4 text-white bg-teal-500 cursor-pointer duration-[0.4s] ease-[cubic-bezier(0.25,1,0.5,1)] h-[25px] rounded-[100%] shadow-[rgba(43,171,160,0.3)_0px_8px_20px_0px] w-[25px]">
                <i class="text-base font-black leading-4 text-white cursor-pointer" aria-hidden="true"></i>
              </span>
            </a>
          </div>
        </div>
      </div>
      <div class="p-4">
        <div class="p-8 bg-white rounded-3xl duration-[0.4s] ease-[cubic-bezier(0.25,1,0.5,1)] shadow-[rgba(43,171,160,0.14)_0px_1px_2px_1px] max-md:p-11">
          <div class="inline-flex justify-center items-center mb-8 h-[90px] rounded-[100%] w-[90px]">
            <img src="https://html.merku.love/talking-minds/assets/images/icons/icon_head_double.svg" alt="Head Double Icon - Talking Minds – Psychotherapist Site Template" class="max-w-full align-middle select-none overflow-x-clip overflow-y-clip">
          </div>
          <div>
            <h3 class="mb-5 text-2xl font-bold leading-8 text-gray-800 max-md:mb-3 max-md:text-2xl max-md:leading-7">
              Couples Counseling
            </h3>
            <p class="mb-8 max-md:mb-5">
              Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusant doloremqe laudantium.
            </p>
            <a href="https://html.merku.love/talking-minds/service_details.html" class="inline-flex relative items-center font-bold leading-5 text-teal-500 cursor-pointer duration-[0.4s] ease-[cubic-bezier(0.25,1,0.5,1)] max-md:leading-4 w-fit whitespace-nowrap hover:bg-hover hover:text-hover">
              <span class="relative font-bold leading-5 text-teal-500 cursor-pointer max-md:leading-4">
                More Info
              </span>
              <span class="flex justify-center items-center ml-2 text-base font-bold leading-4 text-white bg-teal-500 cursor-pointer duration-[0.4s] ease-[cubic-bezier(0.25,1,0.5,1)] h-[25px] rounded-[100%] shadow-[rgba(43,171,160,0.3)_0px_8px_20px_0px] w-[25px]">
                <i class="text-base font-black leading-4 text-white cursor-pointer" aria-hidden="true"></i>
              </span>
            </a>
          </div>
        </div>
      </div>
      <div class="p-4">
        <div class="p-8 bg-white rounded-3xl duration-[0.4s] ease-[cubic-bezier(0.25,1,0.5,1)] shadow-[rgba(43,171,160,0.14)_0px_1px_2px_1px] max-md:p-11">
          <div class="inline-flex justify-center items-center mb-8 h-[90px] rounded-[100%] w-[90px]">
            <img src="https://html.merku.love/talking-minds/assets/images/icons/icon_head.svg" alt="Brain Icon - Talking Minds – Psychotherapist Site Template" class="max-w-full align-middle select-none overflow-x-clip overflow-y-clip">
          </div>
          <div>
            <h3 class="mb-5 text-2xl font-bold leading-8 text-gray-800 max-md:mb-3 max-md:text-2xl max-md:leading-7">
              Relationships
            </h3>
            <p class="mb-8 max-md:mb-5">
              Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusant doloremqe laudantium.
            </p>
            <a href="https://html.merku.love/talking-minds/service_details.html" class="inline-flex relative items-center font-bold leading-5 text-teal-500 cursor-pointer duration-[0.4s] ease-[cubic-bezier(0.25,1,0.5,1)] max-md:leading-4 w-fit whitespace-nowrap hover:bg-hover hover:text-hover">
              <span class="relative font-bold leading-5 text-teal-500 cursor-pointer max-md:leading-4">
                More Info
              </span>
              <span class="flex justify-center items-center ml-2 text-base font-bold leading-4 text-white bg-teal-500 cursor-pointer duration-[0.4s] ease-[cubic-bezier(0.25,1,0.5,1)] h-[25px] rounded-[100%] shadow-[rgba(43,171,160,0.3)_0px_8px_20px_0px] w-[25px]">
                <i class="text-base font-black leading-4 text-white cursor-pointer" aria-hidden="true"></i>
              </span>
            </a>
          </div>
        </div>
      </div>
    </div>
    <div class="pt-12 text-center max-md:pt-10">
      <a href="https://html.merku.love/talking-minds/service.html" class="inline-flex overflow-x-hidden overflow-y-hidden relative items-center px-10 font-bold text-center text-white align-middle bg-teal-500 border border-t border-r border-b border-l border-teal-500 border-solid cursor-pointer select-none delay-[0s,0s] duration-[0.6s,0.6s] ease-[cubic-bezier(0.15,0.85,0.31,1),cubic-bezier(0.15,0.85,0.31,1)] rounded-[30px] shadow-[rgba(43,171,160,0.08)_0px_10px_40px_0px] transition-[transform,-webkit-transform] max-md:px-9 w-fit whitespace-nowrap hover:bg-hover hover:text-hover">
        <span class="relative pt-3 pb-3.5 font-bold text-center text-white cursor-pointer select-none delay-[0s,0s] duration-[0.5s,0.5s] ease-[cubic-bezier(0.15,0.85,0.31,1),cubic-bezier(0.15,0.85,0.31,1)] transition-[transform,-webkit-transform] max-md:pt-2.5 max-md:pb-3">
          All Programs
        </span>
        <span class="ml-2 font-bold text-center text-white cursor-pointer select-none">
          <i class="inline-block font-black leading-5 text-center text-white cursor-pointer select-none max-md:leading-4" aria-hidden="true"></i>
        </span>
      </a>
    </div>
  </div>
</section>

<section class="relative flex pb-40 overflow-hidden bg-white pt-36 max-md:pt-24 max-md:pb-28">
  <div class="flex flex-col items-center w-full pt-5 pb-5 mx-auto max-w-container max-lg:px-5">
    <div class="w-full px-4 mx-auto max-w-container">
      <div class="mb-10 text-center max-sm:mb-5">
        <h2 class="mb-4 text-5xl font-extrabold text-gray-800 leading-[50px] max-md:text-3xl max-md:leading-10">
          What Patients Say
        </h2>
        <p class="mx-auto text-xl leading-8 max-w-[500px] max-md:text-lg max-md:leading-7 max-md:max-w-[430px] max-sm:text-base max-sm:leading-7">
          Lorem Ipsum is simply dummy text of the printing and typesetting industry
        </p>
      </div>

      <div id="testimonial-slider" class="testimonial-slider">
        <!-- Testimonial 1 -->
        <div class="w-auto p-4">
          <article class="relative w-full p-8 bg-white shadow-lg rounded-3xl">
            <ul class="absolute flex top-5 right-5">
              <li><i class="fas fa-star text-amber-400"></i></li>
              <li><i class="fas fa-star text-amber-400"></i></li>
              <li><i class="fas fa-star text-amber-400"></i></li>
              <li><i class="fas fa-star text-amber-400"></i></li>
              <li><i class="fas fa-star text-amber-400"></i></li>
            </ul>
            <div class="flex items-center mb-5">
              <img src="https://html.merku.love/talking-minds/assets/images/meta/author_image_1.png" alt="Kerry Banks" class="mr-4 rounded-full h-14 w-14">
              <div>
                <h3 class="font-bold text-gray-800">Kerry Banks</h3>
                <span class="font-semibold text-teal-500">Housewife</span>
              </div>
            </div>
            <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry...</p>
          </article>
        </div>

        <!-- Testimonial 2 -->
        <div class="p-4">
          <article class="relative p-8 bg-white shadow-lg rounded-3xl">
            <ul class="absolute flex top-5 right-5">
              <li><i class="fas fa-star text-amber-400"></i></li>
              <li><i class="fas fa-star text-amber-400"></i></li>
              <li><i class="fas fa-star text-amber-400"></i></li>
              <li><i class="fas fa-star text-amber-400"></i></li>
              <li><i class="fas fa-star text-amber-400"></i></li>
            </ul>
            <div class="flex items-center mb-5">
              <img src="https://html.merku.love/talking-minds/assets/images/meta/author_image_1.png" alt="Damian York" class="mr-4 rounded-full h-14 w-14">
              <div>
                <h3 class="font-bold text-gray-800">Damian York</h3>
                <span class="font-semibold text-teal-500">Entrepreneur</span>
              </div>
            </div>
            <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry...</p>
          </article>
        </div>
        <div class="p-4">
          <article class="relative p-8 bg-white shadow-lg rounded-3xl">
            <ul class="absolute flex top-5 right-5">
              <li><i class="fas fa-star text-amber-400"></i></li>
              <li><i class="fas fa-star text-amber-400"></i></li>
              <li><i class="fas fa-star text-amber-400"></i></li>
              <li><i class="fas fa-star text-amber-400"></i></li>
              <li><i class="fas fa-star text-amber-400"></i></li>
            </ul>
            <div class="flex items-center mb-5">
              <img src="https://html.merku.love/talking-minds/assets/images/meta/author_image_1.png" alt="Damian York" class="mr-4 rounded-full h-14 w-14">
              <div>
                <h3 class="font-bold text-gray-800">Damian York</h3>
                <span class="font-semibold text-teal-500">Entrepreneur</span>
              </div>
            </div>
            <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry...</p>
          </article>
        </div>
        <div class="p-4">
          <article class="relative p-8 bg-white shadow-lg rounded-3xl">
            <ul class="absolute flex top-5 right-5">
              <li><i class="fas fa-star text-amber-400"></i></li>
              <li><i class="fas fa-star text-amber-400"></i></li>
              <li><i class="fas fa-star text-amber-400"></i></li>
              <li><i class="fas fa-star text-amber-400"></i></li>
              <li><i class="fas fa-star text-amber-400"></i></li>
            </ul>
            <div class="flex items-center mb-5">
              <img src="https://html.merku.love/talking-minds/assets/images/meta/author_image_1.png" alt="Damian York" class="mr-4 rounded-full h-14 w-14">
              <div>
                <h3 class="font-bold text-gray-800">Damian York</h3>
                <span class="font-semibold text-teal-500">Entrepreneur</span>
              </div>
            </div>
            <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry...</p>
          </article>
        </div>
      </div>
    </div>
  </div>
  <script>
    function initializeSlick() {
      if (jQuery("#testimonial-slider").hasClass("slick-initialized")) {
        jQuery("#testimonial-slider").slick("unslick");
      }
      jQuery("#testimonial-slider").slick({
        infinite: true,
        autoplay: true,
        autoplaySpeed: 3000,
        slidesToShow: window.innerWidth < 768 ? 1 : 2,
        slidesToScroll: 1,
        dots: true,
        arrows: false
      });
    }

    jQuery(document).ready(function() {
      initializeSlick();
      jQuery(window).resize(function() {
        initializeSlick();
      });
    });
  </script>
</section>



<section class="relative w-full overflow-hidden">
  <div class="flex flex-row items-center w-full pt-5 pb-5 mx-auto lg:gap-20 max-md:flex-col max-w-container max-xxl:px-5">
    <div class="w-6/12 max-md:mb-5 max-md:w-full">
      <h2 class="text-3xl font-semibold leading-10 text-black mb-7">
        I have high skills in developing and programming
      </h2>
      <p class="box-border">
        I was doing everything in my power to provide me with all the experiences to provide cost-effective and high quality products to satisfy my customers all over the world
      </p>
    </div>

    <!-- Synced Progress Bars -->
    <div class="w-6/12 max-md:w-full">
      <div class="space-y-7"
        x-data="{
              skills: [
                { label: 'Web Development', percent: 95, count: 0, width: 0, animated: false },
                { label: 'Brand Identity', percent: 80, count: 0, width: 0, animated: false },
                { label: 'Logo Design', percent: 90, count: 0, width: 0, animated: false }
              ],
              animationDuration: 2000 // milliseconds
            }">
        <template x-for="(skill, index) in skills" :key="index">
          <div
            x-intersect.once="if (!skill.animated) {
                  skill.animated = true;
                  const stepTime = animationDuration / skill.percent;
                  let interval = setInterval(() => {
                    if (skill.count < skill.percent) skill.count++;
                    else clearInterval(interval);
                  }, stepTime);

                  setTimeout(() => skill.width = skill.percent, 50);
                }">
            <div class="flex justify-between mb-2 font-medium text-left text-black">
              <span x-text="skill.label"></span>
              <span x-text="skill.count + '%'"></span>
            </div>
            <div class="relative w-full h-2 overflow-hidden bg-black rounded-md bg-opacity-10">
              <div
                class="h-full bg-primary rounded-md ease-out transition-[width]"
                :style="`width: ${skill.width}%; transition-duration: ${animationDuration}ms`"></div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</section>