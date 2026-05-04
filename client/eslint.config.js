'use strict';

// ESLint 9 flat config for the RedCrossQuest AngularJS 1.x client.
//
// Migrated from the legacy `.eslintrc` (ESLint 6 + eslint-plugin-angular@4,
// both unmaintained on flat-config) to ESLint 9 + gulp-eslint-new. The
// AngularJS-1.x style plugin is dropped: it has no flat-config-compatible
// successor, and its rules were stylistic only — strict-DI annotations are
// already enforced by ng-annotate at build time.

const globals = require('globals');

module.exports = [
  {
    languageOptions: {
      ecmaVersion: 2020,
      sourceType: 'script',
      globals: {
        // Browser + AngularJS runtime: angular is the global module loader,
        // module/inject are the angular-mocks helpers used in *.spec.js.
        ...globals.browser,
        ...globals.jasmine,
        angular: 'readonly',
        module:  'readonly',
        inject:  'readonly',

        // Vendor libraries pulled in via <script> tags from index.html that
        // are not imported through the AngularJS DI container and therefore
        // appear as bare identifiers in the source:
        //  - $, jQuery: bundled with AngularJS' jQLite shim and used in a
        //    handful of legacy controllers (main.controller.js).
        //  - google: the Google Maps JS API loaded asynchronously in
        //    settings/pointQuete admin screens.
        //  - grecaptcha, recaptchaKey: the reCAPTCHA v2 widget loaded by
        //    index.html and the build-time-injected site key.
        //  - google_translate_element_init: Google Translate's required
        //    callback name set on window for the widget to initialise.
        $:                              'readonly',
        jQuery:                         'readonly',
        google:                         'readonly',
        grecaptcha:                     'readonly',
        recaptchaKey:                   'readonly',
        google_translate_element_init:  'writable'
      }
    },
    rules: {
      // Mirror the previous `eslint:recommended` baseline. We keep this
      // explicit (rather than spreading js.configs.recommended) to avoid an
      // extra @eslint/js dependency for the handful of rules we actually
      // care about on a 14-year-old AngularJS 1 codebase in maintenance.
      'no-undef':                ['error'],
      'no-unused-vars':          ['warn', { args: 'none' }],
      'no-redeclare':            ['error'],
      'no-dupe-keys':            ['error'],
      'no-dupe-args':            ['error'],
      'no-unreachable':          ['error'],
      'no-cond-assign':          ['error', 'except-parens'],
      'no-constant-condition':   ['error', { checkLoops: false }],
      'no-empty':                ['warn', { allowEmptyCatch: true }],
      'no-extra-semi':           ['warn'],
      'no-irregular-whitespace': ['error']
    }
  },
  {
    // Build artefacts and vendor copies that occasionally land under src/
    // (zxcvbn, deploy.json template, etc.) must not be linted.
    ignores: [
      'dist/**',
      '.tmp/**',
      'node_modules/**',
      'bower_components/**',
      'coverage/**',
      'src/**/*.html',
      'gulp/**',
      'gulpfile.js',
      'karma.conf.js',
      'eslint.config.js'
    ]
  }
];
