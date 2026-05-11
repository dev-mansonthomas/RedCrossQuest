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
    // bootstrap-sass 3.4.x is upstream-EOL and relies on a number of Sass
    // features Dart Sass deprecates. Silence the resulting (extremely
    // verbose, hundreds-per-build) warnings so logs stay readable. The
    // CSS output is unchanged - these are forward-compat warnings about
    // syntax that still works today.
    //   - legacy-js-api : gulp-sass 5 still uses the v1 JS API
    //   - import         : @import in bootstrap-sass core (vs @use/@forward)
    //   - global-builtin : top-level lighten/darken/etc.
    //   - slash-div      : "10/6" used as division, not list separator
    //   - if-function    : Sass-flavour if() (vs CSS @if) in bootstrap-sass
    //                       _variables / mixins / our _glyphicons-pro overlay
    //   - color-functions: lighten()/darken() in bootstrap-sass _variables
    // 'mixed-decls' was previously silenced too but Dart Sass now reports
    // that ID as obsolete, so we drop it.
    silenceDeprecations: [
      'legacy-js-api', 'import', 'global-builtin', 'slash-div',
      'if-function', 'color-functions'
    ]
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
    // dart-sass collapses CSS hex escapes ("\2a") into their literal
    // Unicode counterpart ("*"). This breaks bootstrap-sass 3 icon-font
    // rules: "Glyphicons Halflings" only carries glyphs in U+E001..U+E26B,
    // so codepoints like 0x2A, 0x20AC, 0x2601 fall back to the system
    // font (typically rendered as emoji). bootstrap 3 is EOL upstream so
    // we re-encode the affected `.glyphicon-*:before { content: "X" }`
    // rules ourselves. Skip codepoints already in the PUA (already escaped
    // by Sass) so the rewrite is idempotent.
    .pipe($.replace(
      /(\.glyphicon[^{}]*\{\s*content:\s*)"([^"\\])"/g,
      function (m, prefix, ch) {
        var cp = ch.codePointAt(0);
        return (cp >= 0xE000 && cp <= 0xF8FF)
          ? m
          : prefix + '"\\' + cp.toString(16) + '"';
      }
    ))
    .pipe($.autoprefixer()).on('error', conf.errorHandler('Autoprefixer'))
    .pipe($.sourcemaps.write())
    .pipe(gulp.dest(path.join(conf.paths.tmp, '/serve/app/')));
};
