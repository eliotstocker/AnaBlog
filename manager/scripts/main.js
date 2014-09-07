var urlRoot = "http://localhost:63342/AnaBlog/";
var pageTitle = "AnaBlog";

$.ajaxSetup({
  headers: {
    "Authorization": $.cookie('access_token')
  },
  statusCode: {
    401: function() {
      showLogin();
    }
  }
});

var posts = Backbone.Collection.extend({
  page: 1,
  url: function() {
    if(this.page < 2) {
      return urlRoot+"api/";
    } else {
      return urlRoot+"api/?page="+this.page;
    }
  },
  parse: function(resp) {
    this.page = resp.page;
    this.pages = resp.pages;
    this.count = resp.count;
    resp = resp.results;
    return resp;
  },
  model: function(attrs, options) {
    return new post(attrs, options);
  }
});

var post = Backbone.Model.extend({
  url: function() {
    return urlRoot+"api/?post="+this.id;
  },
  saveUrl: function() {
    return urlRoot+"api/"
  },
  parse: function(resp) {
    if(resp.created != undefined) {
      resp.created_display = moment(resp.created * 1000).format("ddd, Do MMM YYYY h:mmA");
    }
    if(resp.updated != undefined) {
      resp.updated_display = moment(resp.updated * 1000).format("ddd, Do MMM YYYY h:mmA");
    }
    return resp;
  },
  sync: function(method, model, options) {
    options = options || {};
    if(method.toLowerCase() == "read") {
      options.url = this.url();
    } else {
      options.url = this.saveUrl();
    }
    return Backbone.sync.apply(this, arguments);
  }
});

var postView = Backbone.View.extend({
  initialize: function() {
    this.listenTo(this.model, "change", this.render);
    _.bindAll(this, 'render', 'attachListeners');
  },
  render: function() {
    if(this.model.isNew()) {
      $("#post-title").val("");
      $("#post-content").empty();
      $("#post-info").empty();
      $("#save").val("Save");
    } else {
      $("#post-title").val(this.model.toJSON().title);
      $("#post-content").html(this.model.toJSON().content);
      $("#post-info").text("Posted on " + this.model.toJSON().created_display + " by " + this.model.toJSON().author.first_name + " " + this.model.toJSON().author.last_name);
      $("#save").val("Update");
    }
    var _this = this;
    $("#save").off('click');
    $("#save").click(function() {
      _this.model.save();
    });
    /*if(editor) {
      editor.setup();
    }*/
    this.attachListeners();
  },
  attachListeners: function() {
    var _this = this;
    $("#post-title").off("keyup").keyup(function() {
      _this.model.set({title: $("#post-title").val()}, { "silent": true });
    });
    $("#post-content").off("input").on('input', function() {
      _this.model.set({content: $("#post-content").html()}, { "silent": true });
    });
  }
});

var menuView = Backbone.View.extend({
  initialize: function() {
    this.listenTo(this.collection, "reset", this.render);
    this.listenTo(this.collection, "add", this.render);
  },
  render: function() {
    $("#entries").empty();
    _(this.collection.models).each(this.renderItem);
  },
  renderItem: function(m) {
    var item = $("<li class=\"menu-item\" id=\""+ m.toJSON().id+"\">"+ m.toJSON().title+"</li>");
    $("#entries").append(item);
    item.click(function() {
      pv = new postView({model: m});
      $(".menu .active").removeClass("active");
      item.addClass("active");
      m.fetch();
      pv.render();
    });
  }
});

$(window).resize(resize);
var pv;
var mv;
var editor;
$(function() {
  if($.cookie('access_token') == undefined) {
    showLogin();
  } else {
    startApp();
  }
  editor = new MediumEditor('#post-content');
  $(".menu-toggle").click(slideMenu);
  resize();
});

function startApp() {
  $("#login").hide();
  var Post = new post();
  pv = new postView({model: Post});
  pv.render();

  var Posts = new posts();
  mv = new menuView({collection: Posts});
  Posts.fetch({reset: true});

  $(".new").click(function() {
    var Post = new post();
    pv = new postView({model: Post});
    pv.render();
    $(".menu .active").removeClass("active");
    $(".new").addClass("active");
  });
}

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
        $.cookie('access_token', json.access_token, {expires: 365});
        startApp();
      },
      error: function() {
        console.log("error");
      }
    });
  });
}

function resize() {
  var w = $(window).width();
  $("#post-title").width(w - 70 - 60);
}