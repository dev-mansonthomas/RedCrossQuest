'use strict';

var fs = require('fs');
var path = require('path');
var gulp = require('gulp');
var conf = require('./conf');
var useref = require('gulp-useref');
var htmlMinifier = require('html-minifier');
var $ = require('gulp-load-plugins')({
  pattern: ['gulp-*', 'del']
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

  // We intentionally keep gulp-sourcemaps (rather than the native
  // gulp.src/dest `sourcemaps:` option introduced in gulp 4): the native
  // option only loads/writes existing sourcemap comments at the src/dest
  // boundary, but in this pipeline `useref` concatenates many node_modules
  // and src/ files into fresh `vendor.js` / `app.js` streams that have no
  // pre-existing sourcemap. We therefore need the mid-pipeline `init()`
  // (just before terser / cleanCss) to create the initial map from the
  // concatenated content; only gulp-sourcemaps offers that.
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
    .pipe($.replace('../../node_modules/bootstrap-sass/assets/fonts/bootstrap/', '../fonts/'))
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

// Only applies for fonts from npm dependencies (currently just bootstrap-sass
// glyphicons). Custom fonts are handled by the "other" task.
// `encoding: false` keeps font binaries intact - vinyl-fs defaults to UTF-8
// in gulp 5 and would otherwise replace bytes >= 0x80 with U+FFFD, doubling
// the file size and producing OTS parsing errors in the browser.
gulp.task('fonts', function () {
  return gulp.src('node_modules/bootstrap-sass/assets/fonts/**/*.{eot,svg,ttf,woff,woff2}', { encoding: false })
    .pipe($.flatten())
    .pipe(gulp.dest(path.join(conf.paths.dist, '/fonts/')));
});

// zxcvbn is loaded asynchronously at runtime by resetPassword.controller.js
// (cf. ZXCVBN_SRC). Stage it under dist/scripts/vendor/ so the deployed bundle
// contains a clean `scripts/vendor/zxcvbn.js` path rather than leaking a
// `node_modules/...` URL into production. The previous approach copied it
// directly from deploy_front.sh after `gulp build`, which split the build
// description across two repositories of truth.
gulp.task('vendorAssets', function () {
  return gulp.src('node_modules/zxcvbn/dist/zxcvbn.js')
    .pipe(gulp.dest(path.join(conf.paths.dist, '/scripts/vendor/')));
});

gulp.task('other', function () {
  var fileFilter = $.filter(function (file) {
    return file.stat.isFile();
  });

  // deploy.json is excluded here: it is fully owned by the `versionNotes`
  // task, which writes the real (date + minified version notes) JSON
  // directly into dist/. Including it via `other` would copy the
  // placeholder src/deploy.json first and rely on task ordering to
  // overwrite it - a brittle race we'd rather not have.
  return gulp.src([
    path.join(conf.paths.src, '/**/*'),
    path.join('!' + conf.paths.src, '/**/*.{html,css,js,scss}'),
    path.join('!' + conf.paths.src, '/deploy.json')
  ], { encoding: false })
    .pipe(fileFilter)
    .pipe(gulp.dest(path.join(conf.paths.dist, '/')));
});

gulp.task('clean', function () {
  return $.del([path.join(conf.paths.dist, '/'), path.join(conf.paths.tmp, '/')]);
});

// Produces dist/deploy.json with the current build timestamp and the
// minified content of versionNotes.html. Replaces the legacy
// client/buildVersionNotes.php + sed dance from build.sh / deploy_front.sh
// (PHP is not available in the node-client image since the toolchain
// modernization). Runs after `other` because that task copies the
// placeholder src/deploy.json to dist/, which we then overwrite.
gulp.task('versionNotes', function (done) {
  var versionNotesPath = path.join(__dirname, '..', 'versionNotes.html');
  var deployJsonOut = path.join(conf.paths.dist, 'deploy.json');

  var notes = fs.readFileSync(versionNotesPath, 'utf8');
  var minified = htmlMinifier.minify(notes, {
    collapseWhitespace: true,
    removeComments: true,
    keepClosingSlash: true,
    conservativeCollapse: true
  });

  // YYYYMMDDHHMMSS in UTC. Legacy bash `date +%Y%m%d%H%M%S` used the host
  // timezone; UTC is deterministic across build environments and matches
  // what GAE reports for deploy events.
  var d = new Date();
  var pad = function (n) { return String(n).padStart(2, '0'); };
  var deployDate = Number(
    d.getUTCFullYear() + pad(d.getUTCMonth() + 1) + pad(d.getUTCDate()) +
    pad(d.getUTCHours()) + pad(d.getUTCMinutes()) + pad(d.getUTCSeconds())
  );

  fs.mkdirSync(path.dirname(deployJsonOut), { recursive: true });
  fs.writeFileSync(deployJsonOut, JSON.stringify({
    deployDate: deployDate,
    deployNotes: minified
  }));
  done();
});

gulp.task('build', gulp.series(gulp.parallel('html', 'fonts', 'other', 'vendorAssets'), 'versionNotes'));
