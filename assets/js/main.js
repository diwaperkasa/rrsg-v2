
window.onscroll = function() {stickyHeaderNav()};


//Sticky Navbar:
function stickyHeaderNav() {

  if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {

    document.querySelector(".top-nav").classList.add("nav-stick");

  } else {

    document.querySelector(".top-nav").classList.remove("nav-stick");

  }

}
