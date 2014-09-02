var urlRoot = "http://localhost:63342/AnaBlog/";
var pageTitle = "AnaBlog";

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
  parse: function(resp) {
    resp.created_display = moment(resp.created * 1000).format("ddd, Do MMM YYYY h:mmA");
    if(resp.updated != undefined) {
      resp.updated_display = moment(resp.updated * 1000).format("ddd, Do MMM YYYY hA");
    }
    return resp;
  }
});

var home = Backbone.View.extend({
  className: "home content",
  initialize: function() {
    this.listenTo(this.collection, "reset", this.render);
  },
  render: function() {
    $("title").text(pageTitle);
    this.$el.html(Handlebars.templates.home({posts: this.collection.toJSON()}));
    $('body').html(this.$el);
  },
  renderPost: function() {

  }
});

var postView = Backbone.View.extend({
  className: "post content",
  initialize: function() {
    this.listenTo(this.model, "change", this.render);
  },
  render: function() {
    $("title").text(pageTitle + " - " + this.model.toJSON().title);
    this.$el.html(Handlebars.templates.post(this.model.toJSON()));
    $('body').html(this.$el);
  }
});

var blogRouter = Backbone.Router.extend({
  routes: {
    ""                   : "home",
    "AnaBlog/index.html" : "home",
    ":id/"               : "page",
    ":id"                : "page"
  },
  mainView: null,
  page: function(id) {
    if(this.mainView != null) {
      this.mainView.close();
    }
    var p = new post({id: id});
    this.mainView = new postView({model: p});
    p.fetch();
  },
  home: function() {
    if(this.mainView != null) {
      this.mainView.close();
    }
    var p = new posts();
    this.mainView = new home({collection: p});
    p.fetch({reset: true});
  }
});

$(function() {
  window.r = new blogRouter();
  Backbone.history.start({pushState: true});
  $("body").on("click", "a", function(e) {
    if(window.history.pushState) {
      e.preventDefault && e.preventDefault();
      window.r.navigate($(e.target).attr("href"), {trigger: true});
      return false;
    } else {
      return true;
    }
  });
});

//extend view to add close function
Backbone.View.prototype.close = function(){
  this.$el.remove();
  if(typeof this.preclose == "function") {
    this.preclose();
  }
}