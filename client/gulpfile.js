/**
 *  Welcome to your gulpfile!
 *  The gulp tasks are splitted in several files in the gulp directory
 *  because putting all here was really too long
 */

'use strict';

var gulp = require('gulp');
var wrench = require('wrench');
var log = require('fancy-log');

/**
 *  This will load all js or coffee files in the gulp directory
 *  in order to load all gulp tasks

wrench.readdirSyncRecursive('./gulp').filter(function(file) {
  return (/\.(js|coffee)$/i).test(file);
}).map(function(file) {

  log('./gulp/' + file);
  require('./gulp/' + file);
});

 */

 require('./gulp/styles.js');
 require('./gulp/scripts.js');
 require('./gulp/inject.js');
 require('./gulp/conf.js');
 require('./gulp/watch.js');
 require('./gulp/unit-tests.js');
 require('./gulp/build.js');
 require('./gulp/server.js');
 require('./gulp/e2e-tests.js');







/**
 *  Default task clean temporaries directories and launch the
 *  main optimization build task
 */
gulp.task('default', gulp.series(['clean']), function () {
  gulp.start('build');
});
