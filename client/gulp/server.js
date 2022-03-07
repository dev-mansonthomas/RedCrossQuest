'use strict';

var path            = require('path');
var gulp            = require('gulp');
var conf            = require('./conf');
var browserSync     = require('browser-sync').create();
var browserSyncSpa  = require('browser-sync-spa');
var util            = require('util');
var proxy           = require('http-proxy-middleware');
var log             = require('fancy-log');

log("server.js");


gulp.task('browser-sync', function() {
  browserSync.init({
    proxy: "http://localhost:8080/"
  });
});







function browserSyncInit(baseDir, browser)
{
  log("baseDir:'"+baseDir+"' browser:'"+browser+"'")
  browser = browser === undefined ? 'default' : browser;

  var routes = null;
  if(baseDir === conf.paths.src || (Array.isArray(baseDir) && baseDir.indexOf(conf.paths.src) !== -1)) {
    routes = {
      '/bower_components': 'bower_components'
    };
  }

  var server = {
    baseDir: baseDir,
    routes: routes
  };

  /*
   * You can add a proxy to your backend by uncommenting the line below.
   * You just have to configure a context which will we redirected and the target url.
   * Example: $http.get('/users') requests will be automatically proxified.
   *
   * For more details and option, https://github.com/chimurai/http-proxy-middleware/blob/v0.9.0/README.md
   */
  server.middleware = proxy('/rest', {target: 'http://localhost:8080/', changeOrigin: true});

  browserSync.init({
    startPath: '/',
    server: server,
    browser: browser
  });
}

browserSync.use(browserSyncSpa({
  selector: '[ng-app]'// Only needed for angular apps
}));

gulp.task('serve', gulp.series(['watch']), function () {
  log("gulp serve called")
  browserSyncInit([path.join(conf.paths.tmp, '/serve'), conf.paths.src]);
});

gulp.task('serve:dist', gulp.series(['build']), function () {
  log("gulp serve:dist called")
  browserSyncInit(conf.paths.dist);
});

gulp.task('serve:e2e', gulp.series(['inject']), function () {
  log("gulp serve:e2e called")
  browserSyncInit([conf.paths.tmp + '/serve', conf.paths.src], []);
});

gulp.task('serve:e2e-dist', gulp.series(['build']), function () {
  log("gulp serve:e2e-dist called")
  browserSyncInit(conf.paths.dist, []);
});
