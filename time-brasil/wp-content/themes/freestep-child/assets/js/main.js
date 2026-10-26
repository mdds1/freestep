document.addEventListener('DOMContentLoaded', function () {
  const hamburger = document.querySelector('.fs-header__hamburger');
  const nav = document.querySelector('.fs-header__nav');

  if (hamburger && nav) {
    hamburger.addEventListener('click', function () {
      nav.classList.toggle('open');
    });
  }

  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth' });
        if (nav && nav.classList.contains('open')) nav.classList.remove('open');
      }
    });
  });
});
