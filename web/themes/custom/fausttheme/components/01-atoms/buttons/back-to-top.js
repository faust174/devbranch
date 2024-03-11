(function (Drupal, once) {
  Drupal.behaviors.backToTop = {
    attach(context) {
      once(
        'backToTop',
        '.back-to-top',
        context,
      ).forEach(function (button) {
        window.addEventListener("scroll", function() {
          if (window.scrollY > 300) {
            button.classList.add("show");
          } else {
            button.classList.remove("show");
          }
        });
        button.addEventListener('click', function () {
          window.scrollTo({
            top: 0,
            behavior: 'smooth',
          });
        });
      });
    },
  };
})(Drupal, once);
