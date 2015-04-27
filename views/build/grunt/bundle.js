module.exports = function(grunt) {

    'use strict';

    var requirejs   = grunt.config('requirejs') || {};
    var clean       = grunt.config('clean') || {};
    var copy        = grunt.config('copy') || {};

    var root        = grunt.option('root');
    var libs        = grunt.option('mainlibs');
    var ext         = require(root + '/tao/views/build/tasks/helpers/extensions')(grunt, root);
    var out         = 'output';

    /**
     * Remove bundled and bundling files
     */
    clean.taobookletbundle = [out];

    /**
     * Compile tao files into a bundle
     */
    requirejs.taobookletbundle = {
        options: {
            baseUrl : '../js',
            dir : out,
            mainConfigFile : './config/requirejs.build.js',
            paths : {
                'taoBooklet' : root + '/taoBooklet/views/js',
                'taoQtiPrint' : root + '/taoQtiPrint/views/js',
                'taoQtiPrintCss' : root + '/taoQtiPrint/views/css',
                'taoQtiItem' : root + '/taoQtiItem/views/js',
                'taoItems' : root + '/taoItems/views/js'
            },
            modules : [{
                name: 'taoBooklet/controller/routes',
                include : ext.getExtensionsControllers(['taoBooklet']),
                exclude : ['mathJax', 'mediaElement', 'css!taoQtiPrintCss/qti.css'].concat(libs)
            }]
        }
    };

    /**
     * copy the bundles to the right place
     */
    copy.taobookletbundle = {
        files: [
            { src: [out + '/taoBooklet/controller/routes.js'],  dest: root + '/taoBooklet/views/js/controllers.min.js' },
            { src: [out + '/taoBooklet/controller/routes.js.map'],  dest: root + '/taoBooklet/views/js/controllers.min.js.map' }
        ]
    };

    grunt.config('clean', clean);
    grunt.config('requirejs', requirejs);
    grunt.config('copy', copy);

    // bundle task
    grunt.registerTask('taobookletbundle', ['clean:taobookletbundle', 'requirejs:taobookletbundle', 'copy:taobookletbundle']);
};
