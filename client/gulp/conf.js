/**
 *  This file contains the variables used in other gulp files
 *  which defines tasks
 *  By design, we only put there very generic config values
 *  which are used in several places to keep good readability
 *  of the tasks
 */

var log = require('fancy-log');
var chalk = require('chalk');

/**
 *  The main paths of your project handle these with care
 */
exports.paths = {
  src: 'src',
  dist: './dist/',
  tmp: '.tmp',
  e2e: 'e2e'
};

/**
 *  Explicit list of vendor JS/CSS to inject into index.html. Order matters
 *  for AngularJS module registration and is the same order wiredep used to
 *  produce from bower.json (verified against the previous .tmp/serve/index.html
 *  snapshot before migration). Replaces the old wiredep+main-bower-files
 *  workflow now that all front-end deps live in node_modules/.
 */
exports.vendor = {
  js: [
    'node_modules/angular/angular.js',
    'node_modules/angular-animate/angular-animate.js',
    'node_modules/angular-cookies/angular-cookies.js',
    'node_modules/angular-touch/angular-touch.js',
    'node_modules/angular-sanitize/angular-sanitize.js',
    'node_modules/angular-messages/angular-messages.js',
    'node_modules/angular-aria/angular-aria.js',
    'node_modules/jquery/dist/jquery.js',
    'node_modules/angular-resource/angular-resource.js',
    'node_modules/angular-route/angular-route.js',
    'node_modules/angular-ui-bootstrap/dist/ui-bootstrap-tpls.js',
    'node_modules/malarkey/dist/malarkey.min.js',
    'node_modules/angular-toastr/dist/angular-toastr.tpls.js',
    'node_modules/moment/moment.js',
    'node_modules/angular-qr-scanner-updated/qr-scanner-u.js',
    'node_modules/angular-qr/lib/qrcode.js',
    'node_modules/angular-qr/src/angular-qr.js',
    'node_modules/angular-audio/app/angular.audio.js',
    'node_modules/moment-timezone/builds/moment-timezone-with-data-10-year-range.min.js',
    'node_modules/angular-local-storage/dist/angular-local-storage.js',
    'node_modules/ngstorage/ngStorage.js',
    'node_modules/angular-jwt/dist/angular-jwt.js',
    'node_modules/zxcvbn/dist/zxcvbn.js',
    'node_modules/ngmap/build/scripts/ng-map.js',
    'node_modules/angular-qr-scanner-updated/src/jsqrcode-combined.min.js',
    'node_modules/bootstrap-sass/assets/javascripts/bootstrap.min.js',
    'node_modules/angular-i18n/angular-locale_fr-fr.js'
  ],
  css: [
    'node_modules/animate.css/animate.css',
    'node_modules/angular-toastr/dist/angular-toastr.css'
  ]
};

/**
 *  Common implementation for an error handler of a Gulp plugin
 */
exports.errorHandler = function(title) {
  'use strict';

  return function(err) {
    log(chalk.red('[' + title + ']'), err.toString());
    this.emit('end');
  };
};
