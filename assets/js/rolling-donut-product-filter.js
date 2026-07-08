(function () {
  const FILTER_SELECTOR = '#custom-filter a';
  const PRODUCTS_SELECTOR = '.filter.products.columns-3';
  const ACTIVE_CLASS = 'bg-yellow-primary';
  const INACTIVE_CLASSES = ['bg-white'];

  let originalHtml = '';
  let ajaxUrl = '';
  let productType = '';

  function getProductsEl() {
    return document.querySelector(PRODUCTS_SELECTOR);
  }

  function getFilterLinks() {
    return document.querySelectorAll(FILTER_SELECTOR);
  }

  function setActiveLink(activeLink) {
    getFilterLinks().forEach((link) => {
      link.classList.remove(ACTIVE_CLASS);
      INACTIVE_CLASSES.forEach((className) => link.classList.add(className));
      link.removeAttribute('aria-current');
    });

    if (!activeLink) {
      return;
    }

    activeLink.classList.add(ACTIVE_CLASS);
    INACTIVE_CLASSES.forEach((className) => activeLink.classList.remove(className));
    activeLink.setAttribute('aria-current', 'true');
  }

  function applyFilter(category, activeLink) {
    const productsEl = getProductsEl();
    if (!productsEl || !originalHtml) {
      return;
    }

    setActiveLink(activeLink);

    if (category === 'all') {
      productsEl.innerHTML = originalHtml;
      return;
    }

    fetch(ajaxUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'filter_products',
        category,
        productType,
      }),
    })
      .then((response) => response.text())
      .then((html) => {
        productsEl.innerHTML = html;
      });
  }

  function resetFilter() {
    const productsEl = getProductsEl();
    if (productsEl && originalHtml) {
      productsEl.innerHTML = originalHtml;
    }

    const allLink = document.querySelector('#custom-filter a[data-filter="all"]');
    setActiveLink(allLink || null);

    const filterList = document.getElementById('custom-filter');
    if (filterList) {
      filterList.scrollLeft = 0;
    }
  }

  function initDragScroll(filterList) {
    let isDragging = false;
    let startX = 0;
    let scrollLeft = 0;
    let moved = false;

    filterList.style.cursor = 'grab';

    filterList.addEventListener('pointerdown', (event) => {
      if (event.pointerType !== 'mouse') {
        return;
      }

      isDragging = true;
      moved = false;
      startX = event.pageX;
      scrollLeft = filterList.scrollLeft;
      filterList.style.cursor = 'grabbing';
      filterList.style.userSelect = 'none';
    });

    window.addEventListener('pointermove', (event) => {
      if (!isDragging) {
        return;
      }

      const delta = event.pageX - startX;
      if (Math.abs(delta) > 3) {
        moved = true;
      }

      filterList.scrollLeft = scrollLeft - delta;
    });

    window.addEventListener('pointerup', () => {
      if (!isDragging) {
        return;
      }

      isDragging = false;
      filterList.style.cursor = 'grab';
      filterList.style.userSelect = '';
    });

    filterList.addEventListener(
      'click',
      (event) => {
        if (moved) {
          event.preventDefault();
          event.stopPropagation();
        }
      },
      true
    );
  }

  function init(config) {
    ajaxUrl = config.ajaxUrl || '';
    productType = config.productType || '';

    const productsEl = getProductsEl();
    const filterLinks = getFilterLinks();

    if (!productsEl || filterLinks.length === 0) {
      return;
    }

    originalHtml = productsEl.innerHTML;
    setActiveLink(document.querySelector('#custom-filter a[data-filter="all"]'));

    filterLinks.forEach((link) => {
      link.addEventListener('click', (event) => {
        event.preventDefault();
        applyFilter(link.dataset.filter || 'all', link);
      });
    });

    const filterList = document.getElementById('custom-filter');
    if (filterList) {
      initDragScroll(filterList);
    }
  }

  window.matrixRdResetProductFilter = resetFilter;

  document.addEventListener('DOMContentLoaded', () => {
    if (!window.matrixRdProductFilter) {
      return;
    }

    init({
      ajaxUrl: window.matrixRdProductFilter.ajaxUrl || '',
      productType: window.matrixRdProductFilter.productType || '',
    });
  });
})();
