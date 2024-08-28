
window.onscroll = function() {
  stickyHeaderNav();
};

//Sticky Navbar:
function stickyHeaderNav() {
  const height = outerHeight(document.querySelector('#div-gpt-ad-1698301711475-0'));

  if (document.body.scrollTop > height || document.documentElement.scrollTop > height) {
    document.querySelector(".top-nav").classList.add("nav-stick");
    document.querySelector('main').style.marginTop = `${document.querySelector('header').offsetHeight}px`;
  } else {
    document.querySelector(".top-nav").classList.remove("nav-stick");
    document.querySelector('main').style.marginTop = '0px';
  }
}

function outerHeight(element) {
  const height = element.offsetHeight,
    style = window.getComputedStyle(element)

  return ['top', 'bottom']
    .map(side => parseInt(style[`margin-${side}`]))
    .reduce((total, side) => total + side, height)
}

$('.slider-menu .dropdown-toggle').click(function() {
  const subMenuId = $(this).data('sub-menu-id');

  if ($(`.slider-dropdown #${subMenuId}`).hasClass('show')) {
    $(`.slider-dropdown #${subMenuId}`).removeClass('show');
    $(this).removeClass('show');

    return;
  }

  const width = $(window).width();
  const position = $(this).offset();
  const elWidth = $(`.slider-dropdown #${subMenuId}`).width();

  $('.slider-dropdown .dropdown-menu').removeClass('show');
  $('.slider-menu .dropdown-toggle').removeClass('show');
  $(`.slider-dropdown #${subMenuId}`)
    .addClass('show')
    .css('left', (position.left + elWidth > width) ? (position.left - elWidth) : position.left);

  $(this).addClass('show');
});

$('.nav-scroller nav').scroll(function() {
  const lastPosition = $('.slider-menu .dropdown-toggle.show').offset();

  if (!lastPosition) {
    return;
  }

  const width = $(window).width();
  const elWidth = $(`.slider-dropdown .dropdown-menu.show`).width();
  const left = lastPosition.left - elWidth;

  $(`.slider-dropdown .dropdown-menu.show`)
    .css('left', Math.min(Math.max(left, 0), width - elWidth));
});

$(`.slider-menu .dropdown-toggle`).each(function(index, el) {
  const subMenuId = $(el).data('sub-menu-id');

  if ($(`.slider-dropdown #${subMenuId}`).length) {
    $(el).removeClass('d-none');
  }
});