'use strict';

var path = require('path');
var gulp = require('gulp');
var conf = require('./conf');

var browserSync = require('browser-sync');

var $ = require('gulp-load-plugins')();

// gulp-sass 5 requires explicit injection of the Sass compiler implementation
// (Dart Sass / `sass` package) — it no longer bundles its own.
var sassCompiler = require('sass');
var sass = require('gulp-sass')(sassCompiler);

gulp.task('styles', function styles() {
  return buildStyles();
});

gulp.task('styles-reload', gulp.series('styles', function stylesReload() {
  return buildStyles()
    .pipe(browserSync.stream());
}));

var buildStyles = function() {
  var sassOptions = {
    outputStyle: 'expanded',
    // bootstrap-sass 3.4.x relies on legacy SCSS division semantics; silence
    // the (extremely verbose) Dart Sass deprecation warnings so build logs
    // stay readable. The CSS output is unchanged.
    silenceDeprecations: ['legacy-js-api', 'import', 'global-builtin', 'slash-div', 'mixed-decls']
  };

  var injectFiles = gulp.src([
    path.join(conf.paths.src, '/app/**/*.scss'),
    path.join('!' + conf.paths.src, '/app/index.scss')
  ], { read: false });

  var injectOptions = {
    transform: function(filePath) {
      filePath = filePath.replace(conf.paths.src + '/app/', '');
      return '@import "' + filePath + '";';
    },
    starttag: '// injector',
    endtag: '// endinjector',
    addRootSlash: false
  };


  return gulp.src([
    path.join(conf.paths.src, '/app/index.scss')
  ])
    .pipe($.inject(injectFiles, injectOptions))
    .pipe($.sourcemaps.init())
    .pipe(sass(sassOptions)).on('error', conf.errorHandler('Sass'))
    .pipe($.autoprefixer()).on('error', conf.errorHandler('Autoprefixer'))
    .pipe($.sourcemaps.write())
    .pipe(gulp.dest(path.join(conf.paths.tmp, '/serve/app/')));
};
