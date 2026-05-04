'use strict';

var path = require('path');
var gulp = require('gulp');
var conf = require('./conf');

var $ = require('gulp-load-plugins')();

var browserSync = require('browser-sync');

gulp.task('inject', gulp.series(gulp.parallel('scripts', 'styles'), function injectAll() {
  var injectStyles = gulp.src([
    path.join(conf.paths.tmp, '/serve/app/**/*.css'),
    path.join('!' + conf.paths.tmp, '/serve/app/vendor.css')
  ], { read: false });

  var injectScripts = gulp.src([
    path.join(conf.paths.src, '/app/**/*.module.js'),
    path.join(conf.paths.src, '/app/**/*.js'),
    path.join('!' + conf.paths.src, '/app/**/*.spec.js'),
    path.join('!' + conf.paths.src, '/app/**/*.mock.js'),
  ])
  .pipe($.angularFilesort()).on('error', conf.errorHandler('AngularFilesort'));

  var injectOptions = {
    ignorePath: [conf.paths.src, path.join(conf.paths.tmp, '/serve')],
    addRootSlash: false
  };

  // Vendor injection (replaces wiredep). gulp.src reads files from the
  // project root with base '.' so file.relative is e.g.
  // 'node_modules/jquery/dist/jquery.js'. With relative:true gulp-inject then
  // emits '../node_modules/...' tags relative to src/index.html.
  // The starttag/endtag pair is pinned to <!-- vendor:{ext} --> /
  // <!-- endvendor --> because gulp-inject's default endtag is
  // <!-- endinject --> regardless of `name`, which would otherwise greedily
  // swallow the unrelated <!-- inject:js --> block further down.
  var vendorJs = gulp.src(conf.vendor.js, { read: false, base: '.' });
  var vendorCss = gulp.src(conf.vendor.css, { read: false, base: '.' });

  var vendorInjectOptions = {
    starttag: '<!-- vendor:{{ext}} -->',
    endtag: '<!-- endvendor -->',
    relative: true,
    addRootSlash: false
  };

  return gulp.src(path.join(conf.paths.src, '/*.html'))
    .pipe($.inject(injectStyles, injectOptions))
    .pipe($.inject(injectScripts, injectOptions))
    .pipe($.inject(vendorJs, vendorInjectOptions))
    .pipe($.inject(vendorCss, vendorInjectOptions))
    .pipe(gulp.dest(path.join(conf.paths.tmp, '/serve')));
}));

gulp.task('inject-reload', gulp.series('inject', function injectReload(done) {
  browserSync.reload();
  done();
}));
