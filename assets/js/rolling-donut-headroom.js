/**
 * Headroom-style header state for Rolling Donut navbar (Alpine x-data).
 *
 * When pinned, the promo topbar is hidden (x-show="!isPinned"). The spacer must
 * keep the *pre-pin* in-flow height so the page does not jump when that bar
 * disappears and the header becomes fixed.
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
    /** Height reserved in document flow while pinned (captured before topbar hides). */
    flowHeight: 0,
    pinOffset: 60,
    tolerance: 5,

    init() {
      this.lastScrollY = window.scrollY;
      this.$nextTick(() => this.measure());
      window.addEventListener('resize', () => {
        this.measure();
        if (window.innerWidth > 1210) {
          this.open = false;
        }
      });
      window.addEventListener('scroll', () => this.onScroll(), { passive: true });
    },

    measure() {
      if (!this.$refs.headerBar) {
        return;
      }

      // While pinned the promo topbar is hidden — do not overwrite flowHeight
      // with the shorter fixed-bar measurement (that causes a layout jump).
      this.headerHeight = this.$refs.headerBar.offsetHeight;
      if (!this.isPinned) {
        this.flowHeight = this.headerHeight;
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
      if (!this.isPinned) {
        return '0px';
      }
      const height = this.flowHeight || this.headerHeight;
      return height ? height + 'px' : '0px';
    },

    captureFlowHeight() {
      if (this.$refs.headerBar) {
        this.flowHeight = this.$refs.headerBar.offsetHeight;
      }
    },

    onScroll() {
      if (this.isCartOrCheckoutPage) {
        return;
      }

      const wasPinned = this.isPinned;
      const wasVisible = this.isVisible;
      const y = window.scrollY;
      const delta = y - this.lastScrollY;

      if (y <= 0) {
        this.isPinned = false;
        this.isVisible = true;
      } else if (y > this.pinOffset) {
        // Capture full in-flow height (promo topbar + nav) before Alpine hides the topbar.
        if (!this.isPinned) {
          this.captureFlowHeight();
        }
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

      if (wasPinned && !this.isPinned) {
        this.$nextTick(() => this.measure());
      }

      if (wasPinned !== this.isPinned || wasVisible !== this.isVisible) {
        window.dispatchEvent(new CustomEvent('matrix_rd_header_layout_change'));
      }

      this.lastScrollY = y <= 0 ? 0 : y;
    },
  };
};
