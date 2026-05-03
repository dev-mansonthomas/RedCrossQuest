/**
 *  Welcome to your gulpfile!
 *  The gulp tasks are splitted in several files in the gulp directory
 *  because putting all here was really too long
 */

'use strict';

var gulp = require('gulp');

/**
 *  Load gulp task modules in dependency order. gulp 4+ resolves task name
 *  strings (e.g. `gulp.series('inject')`) at the time `series` / `parallel`
 *  is invoked, not at task run time, so any task referenced by name must be
 *  registered first. Alphabetical loading (the previous wrench behaviour)
 *  no longer works because `build.js` references `inject`, etc.
 */
require('./gulp/conf');           // shared config (no tasks)
require('./gulp/scripts');        // leaf tasks: scripts, scripts-reload
require('./gulp/styles');         // leaf tasks: styles, styles-reload
require('./gulp/inject');         // depends on scripts + styles
require('./gulp/build');          // depends on inject + partials
require('./gulp/watch');          // depends on inject
require('./gulp/unit-tests');     // depends on scripts + watch
require('./gulp/server');         // depends on watch + build + inject

/**
 *  Default task: clean temporary directories and launch the main
 *  optimization build task. gulp 4+ uses gulp.series instead of deps array.
 */
gulp.task('default', gulp.series('clean', 'build'));
