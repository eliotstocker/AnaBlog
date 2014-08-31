var posts = Backbone.Collection.extend({
  page: 1,
  url: function() {
    if(this.page < 2) {
      return "api";
    } else {
      return "api?page="+this.page;
    }
  },
  parse: function(resp) {
    this.page = resp.page;
    this.pages = resp.pages;
    this.count = resp.count;
    resp = resp.results;
    return resp;
  }
});

var home = Backbone.View.extend({
  initialize: function() {
    this.listenTo(this.collection, "reset", this.render);
  },
  render: function() {
    console.log(this.collection.toJSON());
    this.$el.html(Handlebars.templates.home({posts: this.collection.toJSON()}));
    $('body').html(this.$el);
  },
  renderPost: function() {

  }
});

$(function() {
  var p = new posts();
  var h = new home({collection: p});
  p.fetch({reset: true});
});