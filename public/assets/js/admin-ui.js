(function () {
  'use strict';

  var sidebar = document.querySelector('[data-admin-sidebar]');
  var toggleButton = document.querySelector('[data-admin-sidebar-toggle]');

  if (!sidebar || !toggleButton) {
    return;
  }

  var closeSidebar = function () {
    sidebar.classList.remove('open');
    toggleButton.setAttribute('aria-expanded', 'false');
  };

  var openSidebar = function () {
    sidebar.classList.add('open');
    toggleButton.setAttribute('aria-expanded', 'true');
  };

  toggleButton.addEventListener('click', function () {
    if (sidebar.classList.contains('open')) {
      closeSidebar();
      return;
    }

    openSidebar();
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeSidebar();
    }
  });

  window.addEventListener('resize', function () {
    if (window.innerWidth > 768) {
      closeSidebar();
    }
  });
})();
