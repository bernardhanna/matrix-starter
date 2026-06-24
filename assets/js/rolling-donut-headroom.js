/**
 * Headroom-style header state for Rolling Donut navbar (Alpine x-data).
 */
window.matrixRdHeadroom = function (config) {
  config = config || {};
  return {
    open: false,
    showSearch: false,
    isCartOrCheckoutPage: !!config.cartOrCheckout,
    lastScrollY: 0,
    isPinned: false,
    isVisible: true,
    headerHeight: 0,
    pinOffset: 60,
    tolerance: 5,

    init() {
      this.lastScrollY = window.scrollY;
      this.$nextTick(() => this.measure());
      window.addEventListener('resize', () => {
        this.measure();
        if (window.innerWidth > 1149) {
          this.open = false;
        }
      });
      window.addEventListener('scroll', () => this.onScroll(), { passive: true });
    },

    measure() {
      if (this.$refs.headerBar) {
        this.headerHeight = this.$refs.headerBar.offsetHeight;
      }
    },

    adminBarOffset() {
      const bar = document.getElementById('wpadminbar');
      return bar ? bar.offsetHeight : 0;
    },

    get pinnedStyle() {
      if (!this.isPinned) {
        return '';
      }
      return 'top: ' + this.adminBarOffset() + 'px';
    },

    get spacerHeight() {
      if (!this.isPinned || !this.headerHeight) {
        return '0px';
      }
      return this.headerHeight + 'px';
    },

    onScroll() {
      if (this.isCartOrCheckoutPage) {
        return;
      }

      const y = window.scrollY;
      const delta = y - this.lastScrollY;

      if (y <= 0) {
        this.isPinned = false;
        this.isVisible = true;
      } else if (y > this.pinOffset) {
        this.isPinned = true;
        if (delta < -this.tolerance) {
          this.isVisible = true;
        } else if (delta > this.tolerance) {
          this.isVisible = false;
          this.open = false;
          this.showSearch = false;
        }
      } else {
        this.isPinned = false;
        this.isVisible = true;
      }

      this.lastScrollY = y <= 0 ? 0 : y;
    },
  };
};
