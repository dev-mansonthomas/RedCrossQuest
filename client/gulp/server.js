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

  // Expose node_modules so dev-mode injected vendor scripts (../node_modules/...)
  // resolve. Only useful for `serve` (raw src + .tmp) since `serve:dist` is fully
  // bundled by useref/rev and no longer references node_modules at runtime;
  // routing it in both cases is harmless.
  // /scripts/vendor mirrors the layout produced by the gulp `vendorAssets`
  // task at build time (dist/scripts/vendor/zxcvbn.js), so the runtime path
  // hardcoded in resetPassword.controller.js (ZXCVBN_SRC) resolves both in
  // dev (via this route) and in prod (via the static file under dist/).
  var routes = {
    '/node_modules': 'node_modules',
    '/scripts/vendor': 'node_modules/zxcvbn/dist'
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
