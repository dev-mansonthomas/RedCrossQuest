'use strict';

var path = require('path');
var gulp = require('gulp');
var conf = require('./conf');

var browserSync = require('browser-sync');

var $ = require('gulp-load-plugins')();
// gulp-eslint-new is the maintained ESLint 9 + flat-config replacement for
// gulp-eslint@6 (unmaintained, ESLint <=8 only). It is loaded directly
// because gulp-load-plugins resolves keys from camelCase package names and
// `eslintNew` would not match our existing `$.eslint` call sites.
var eslint = require('gulp-eslint-new');


gulp.task('scripts-reload', function() {
  return buildScripts()
    .pipe(browserSync.stream());
});

gulp.task('scripts', function() {
  return buildScripts();
});

function buildScripts() {
  return gulp.src(path.join(conf.paths.src, '/app/**/*.js'))
    .pipe(eslint())
    .pipe(eslint.format())
    .pipe($.size())
};
