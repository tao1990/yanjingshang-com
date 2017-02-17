//lazyload
$(document).ready(function(){$("img").not(".not_lazyload").lazyload({placeholder:"images/white.gif", effect:"fadeIn"});});


//returnTop
$(window).bind("scroll",function(){var t=$(document).height(),n=$(window).scrollTop();n>150?$("#goTop").show():$("#goTop").hide()});

//foucs
var slider =
  Swipe(document.getElementById('slider'), {
    auto: 3000,
    continuous: true,
    callback: function(pos) {

      var i = bullets.length;
      while (i--) {
        bullets[i].className = ' ';
      }
      bullets[pos].className = 'on';
    }
  });
var bullets = document.getElementById('position').getElementsByTagName('li');