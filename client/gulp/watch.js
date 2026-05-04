'use strict';

var path = require('path');
var gulp = require('gulp');
var conf = require('./conf');

var browserSync = require('browser-sync');

gulp.task('watch', gulp.series('inject', function watchAll(done) {

  // package.json triggers a re-inject so vendor list changes (gulp/conf.js
  // is keyed off node_modules/) are picked up without restarting `gulp serve`.
  gulp.watch([path.join(conf.paths.src, '/*.html'), 'package.json'], gulp.series('inject-reload'));

  // gulp 4 watch returns a chokidar instance: subscribe to specific events.
  // Pure file changes -> recompile only styles; add/unlink -> full inject.
  var stylesWatcher = gulp.watch([
    path.join(conf.paths.src, '/app/**/*.css'),
    path.join(conf.paths.src, '/app/**/*.scss')
  ]);
  stylesWatcher.on('change', function () { gulp.series('styles-reload')(); });
  stylesWatcher.on('add',    function () { gulp.series('inject-reload')(); });
  stylesWatcher.on('unlink', function () { gulp.series('inject-reload')(); });

  var scriptsWatcher = gulp.watch(path.join(conf.paths.src, '/app/**/*.js'));
  scriptsWatcher.on('change', function () { gulp.series('scripts-reload')(); });
  scriptsWatcher.on('add',    function () { gulp.series('inject-reload')(); });
  scriptsWatcher.on('unlink', function () { gulp.series('inject-reload')(); });

  gulp.watch(path.join(conf.paths.src, '/app/**/*.html'))
    .on('change', function (filePath) { browserSync.reload(filePath); });

  done();
}));
