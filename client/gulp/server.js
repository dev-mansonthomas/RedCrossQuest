'use strict';

var path = require('path');
var gulp = require('gulp');
var conf = require('./conf');

var browserSync = require('browser-sync');
var browserSyncSpa = require('browser-sync-spa');

// http-proxy-middleware v3 ships a named export `createProxyMiddleware`
// and removed the legacy default-export shorthand used in v0.x.
var createProxyMiddleware = require('http-proxy-middleware').createProxyMiddleware;

function browserSyncInit(baseDir, browser) {
  browser = browser === undefined ? 'default' : browser;

  // Expose bower_components for both dev (src) and dist serves. The built
  // index.html still references `../bower_components/{angular-i18n,zxcvbn}/*`
  // verbatim; in production GCP/deploy_front.sh copies these into dist/, but
  // for local serve:dist we route them straight from the source tree.
  var routes = {
    '/bower_components': 'bower_components'
  };

  var server = {
    baseDir: baseDir,
    routes: routes
  };

  /*
   * Proxy REST calls to the PHP backend reachable on localhost:8080
   * (the entrypoint forwards that port to the nginx container).
   */
  server.middleware = createProxyMiddleware({
    pathFilter: '/rest',
    target: 'http://localhost:8080/',
    changeOrigin: true
  });

  browserSync.instance = browserSync.init({
    startPath: '/',
    server: server,
    browser: browser
  });
}

browserSync.use(browserSyncSpa({
  selector: '[ng-app]'// Only needed for angular apps
}));

gulp.task('serve', gulp.series('watch', function serve(done) {
  browserSyncInit([path.join(conf.paths.tmp, '/serve'), conf.paths.src]);
  done();
}));

gulp.task('serve:dist', gulp.series('build', function serveDist(done) {
  browserSyncInit(conf.paths.dist);
  done();
}));

gulp.task('serve:e2e', gulp.series('inject', function serveE2e(done) {
  browserSyncInit([conf.paths.tmp + '/serve', conf.paths.src], []);
  done();
}));

gulp.task('serve:e2e-dist', gulp.series('build', function serveE2eDist(done) {
  browserSyncInit(conf.paths.dist, []);
  done();
}));
