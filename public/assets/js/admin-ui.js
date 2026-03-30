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

// Submenu management
(function () {
  'use strict';

  // Initialize all submenu toggles
  var submenuToggles = document.querySelectorAll('[data-submenu-toggle]');

  submenuToggles.forEach(function (toggle) {
    var submenuId = toggle.getAttribute('data-submenu-toggle');
    var submenu = document.querySelector('[data-submenu="' + submenuId + '"]');
    var group = toggle.closest('.admin-sidebar-group');

    if (!submenu || !group) {
      return;
    }

    // Check if any sublink is active on page load
    var hasActiveSublink = submenu.querySelector('.admin-sidebar-sublink.active');
    if (hasActiveSublink) {
      group.classList.add('open');
    }

    // Toggle submenu on click
    toggle.addEventListener('click', function (event) {
      event.preventDefault();

      var isOpen = group.classList.contains('open');

      // Close all other submenus
      document.querySelectorAll('.admin-sidebar-group.open').forEach(function (openGroup) {
        if (openGroup !== group) {
          openGroup.classList.remove('open');
        }
      });

      // Toggle current submenu
      if (isOpen) {
        group.classList.remove('open');
      } else {
        group.classList.add('open');
      }
    });
  });
})();
