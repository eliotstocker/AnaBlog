var post = Backbone.Model.extend({
  url: "../api/"
});

$(window).resize(resize);
$(function() {
  if($.cookie('access_token') == undefined) {
    showLogin();
  }

  $(".menu-toggle").click(slideMenu);
  var editor = new MediumEditor('#post-content');
  resize();
});

function slideMenu(e) {
  e.preventDefault && e.preventDefault();
  if($(".menu").position().left == 0) {
    $(".menu-toggle").removeClass("active");
    $(".menu").removeClass("open");
    $("#editor-content").removeClass("slide");
  } else {
    $(".menu-toggle").addClass("active");
    $(".menu").addClass("open");
    $("#editor-content").addClass("slide");
  }
  return false;
}

function showLogin() {
  $("#login").fadeIn();
  $("#login-btn").click(function() {
    var data = {
      email: $("#email").val(),
      password: $("#password").val()
    }
    $.ajax({
      url: "../api/",
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify(data),
      dataType: "json",
      success: function(json) {
        $.cookie('access_token', json.access_token);
      },
      error: function() {
        console.log("error");
      }
    });
  });
}

function resize() {
  var w = $(window).width();
  $("#post-title").width(w - 70);
}