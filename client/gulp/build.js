'use strict';

var path = require('path');
var gulp = require('gulp');
var conf = require('./conf');
var useref = require('gulp-useref');
var $ = require('gulp-load-plugins')({
  pattern: ['gulp-*', 'main-bower-files', 'del']
});

gulp.task('partials', function () {
  return gulp.src([
    path.join(conf.paths.src, '/app/**/*.html'),
    path.join(conf.paths.tmp, '/serve/app/**/*.html')
  ])
    .pipe($.htmlmin({
      collapseWhitespace: true,
      removeComments: true,
      keepClosingSlash: true
    }))
    .pipe($.angularTemplatecache('templateCacheHtml.js', {
      module: 'redCrossQuestClient',
      root: 'app'
    }))
    .pipe(gulp.dest(conf.paths.tmp + '/partials/'));
});

// `partials` reads from `.tmp/serve/app` which is produced by the `styles`
// sub-task of `inject`. In gulp 4/5 we therefore have to run `inject` first
// (was an implicit ordering side-effect under gulp 3 where deps could fan-out
// in any order but the directory ended up created before partials read it).
gulp.task('html', gulp.series('inject', 'partials', function htmlBundle() {
  var partialsInjectFile = gulp.src(path.join(conf.paths.tmp, '/partials/templateCacheHtml.js'), { read: false });
  var partialsInjectOptions = {
    starttag: '<!-- inject:partials -->',
    ignorePath: path.join(conf.paths.tmp, '/partials'),
    addRootSlash: false
  };

  // Use predicate-function filters: glob matching in gulp-filter v7 was
  // unreliable with the absolute paths produced by gulp-useref/rev in this
  // pipeline (no file matched, so the inner replace/cleanCss/htmlmin steps
  // were silently bypassed).
  var htmlFilter = $.filter(function (file) { return file.extname === '.html'; }, { restore: true });
  var jsFilter = $.filter(function (file) { return file.extname === '.js'; }, { restore: true });
  var cssFilter = $.filter(function (file) { return file.extname === '.css'; }, { restore: true });
  // Sourcemap files inherit `revOrigPath` from their source via gulp-sourcemaps,
  // which causes gulp-rev-replace to register them as renames and rewrite HTML
  // refs to point to `maps/*.map` instead of the actual asset. Pull them out of
  // the stream around revReplace.
  var mapFilter = $.filter(function (file) { return file.extname !== '.map'; }, { restore: true });

  return gulp.src(path.join(conf.paths.tmp, '/serve/*.html'))
    .pipe($.inject(partialsInjectFile, partialsInjectOptions))
    .pipe(useref())
    .pipe($.rev())
    .pipe(jsFilter)
    .pipe($.sourcemaps.init())
    .pipe($.ngAnnotate())
    .pipe($.terser({ format: { comments: 'some' } })).on('error', conf.errorHandler('Terser'))
    .pipe($.sourcemaps.write('maps'))
    .pipe(jsFilter.restore)
    .pipe(cssFilter)
    .pipe($.sourcemaps.init())
    .pipe($.replace('../../bower_components/bootstrap-sass/assets/fonts/bootstrap/', '../fonts/'))
    .pipe($.cleanCss({ processImport: false }))
    .pipe($.sourcemaps.write('maps'))
    .pipe(cssFilter.restore)
    .pipe(mapFilter)
    .pipe($.revReplace())
    .pipe(htmlFilter)
    .pipe($.htmlmin({
      collapseWhitespace: true,
      removeComments: true,
      keepClosingSlash: true,
      conservativeCollapse: true
    }))
    .pipe(htmlFilter.restore)
    .pipe(mapFilter.restore)
    .pipe(gulp.dest(path.join(conf.paths.dist, '/')))
    .pipe($.size({ title: path.join(conf.paths.dist, '/'), showFiles: true }));
}));

// Only applies for fonts from bower dependencies
// Custom fonts are handled by the "other" task
// `encoding: false` keeps font binaries intact - vinyl-fs defaults to UTF-8
// in gulp 5 and would otherwise replace bytes >= 0x80 with U+FFFD, doubling
// the file size and producing OTS parsing errors in the browser.
gulp.task('fonts', function () {
  return gulp.src($.mainBowerFiles(), { allowEmpty: true, encoding: false })
    .pipe($.filter('**/*.{eot,svg,ttf,woff,woff2}'))
    .pipe($.flatten())
    .pipe(gulp.dest(path.join(conf.paths.dist, '/fonts/')));
});

gulp.task('other', function () {
  var fileFilter = $.filter(function (file) {
    return file.stat.isFile();
  });

  return gulp.src([
    path.join(conf.paths.src, '/**/*'),
    path.join('!' + conf.paths.src, '/**/*.{html,css,js,scss}')
  ], { encoding: false })
    .pipe(fileFilter)
    .pipe(gulp.dest(path.join(conf.paths.dist, '/')));
});

gulp.task('clean', function () {
  return $.del([path.join(conf.paths.dist, '/'), path.join(conf.paths.tmp, '/')]);
});

gulp.task('build', gulp.parallel('html', 'fonts', 'other'));
