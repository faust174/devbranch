(function (Drupal, once) {
  Drupal.behaviors.mainMenu = {
    attach(context) {
      once(
        'mainMenu',
        '.header',
        context).forEach( function () {
        const toggleExpand = context.querySelector('#toggle-expand');
        const menu = context.querySelector('#main-nav');
        if (menu) {
          const expandMenu = menu.querySelectorAll('.expand-sub');

          // Mobile Menu Show/Hide.
          toggleExpand.addEventListener('click', function (e) {
            toggleExpand.classList.toggle('toggle-expand--open');
            menu.classList.toggle('main-nav--open');
            e.preventDefault();
          });

          // Expose mobile sub menu on click.
          expandMenu.forEach(function (item) {
            item.addEventListener('click', function (e) {
              const menuItem = e.currentTarget;
              const subMenu = menuItem.nextElementSibling;

              menuItem.classList.toggle('expand-sub--open');
              subMenu.classList.toggle('main-menu--sub-open');
            });
          });
        }
      });
    },
  };
})(Drupal, once);
