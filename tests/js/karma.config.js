/**
 * nextCloud - nextnotes
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Janis Koehr <janiskoehr@icloud.com>
 * @copyright Janis Koehr 2016
 */
module.exports = function(config) {
	// can't use wildcard due to loading order
	var srcFiles = [
		'js/clearsearch.min.js',
		'js/simplemde.min.js',
		'js/nextnotesapp.js',
		'js/nextnotesnotes.js',
		'js/nextnotestags.js',
		'js/nextnotesview.js'
	];

	var testFiles = [
		'tests/js/*Spec.js'
	];

	var basePath = '../../';
	var instancePath = '../../';

	var coreModules = require(instancePath + '../../core/js/core.json');
	var coreLibs = [
		instancePath + 'core/js/tests/lib/sinon-1.15.4.js',
		instancePath + 'core/js/tests/specHelper.js'
	];

	coreLibs = coreLibs.concat(coreModules.vendor.map(function prependPath(path) {
		return instancePath + 'core/vendor/' + path;
	}));

	coreLibs = coreLibs.concat(coreModules.modules.map(function prependPath(path) {
		return instancePath + 'core/js/' + path;
	}));

	var files = [].concat(coreLibs, srcFiles, testFiles);
	config.set({

		// base path, that will be used to resolve files and exclude
		basePath: basePath,

		// frameworks to use
		frameworks: ['jasmine'],

		// list of files / patterns to load in the browser
		files: files,

		// list of files to exclude
		exclude: [

		],

		proxies: {
			// prevent warnings for images
			'/context.html//core/img/': 'http://localhost:9876/base/core/img/',
			'/context.html//core/css/': 'http://localhost:9876/base/core/css/',
			'/context.html//core/fonts/': 'http://localhost:9876/base/core/fonts/'
		},

		// test results reporter to use
		// possible values: 'dots', 'progress', 'junit', 'growl', 'coverage'
		reporters: ['progress'],

		junitReporter: {
			outputFile: 'tests/autotest-results-js.xml'
		},

		// web server port
		port: 9876,

		// enable / disable colors in the output (reporters and logs)
		colors: true,

		// level of logging
		// possible values: config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG
		logLevel: config.LOG_INFO,

		// enable / disable watching file and executing tests whenever any file changes
		autoWatch: true,

		// Start these browsers, currently available:
		// - Chrome
		// - ChromeCanary
		// - Firefox
		// - Opera (has to be installed with `npm install karma-opera-launcher`)
		// - Safari (only Mac; has to be installed with `npm install karma-safari-launcher`)
		// - PhantomJS
		// - IE (only Windows; has to be installed with `npm install karma-ie-launcher`)
		browsers: ['Firefox','PhantomJS'],

		// If browser does not capture in given timeout [ms], kill it
		captureTimeout: 60000,

		// Continuous Integration mode
		// if true, it capture browsers, run tests and exit
		singleRun: false
	});
};