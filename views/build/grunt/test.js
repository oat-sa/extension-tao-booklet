module.exports = function(grunt) {
    'use strict';

    var testUrl     = 'http://127.0.0.1:' + grunt.option('testPort');
    var root        = grunt.option('root');

    var testRunners = root + '/taoBooklet/views/js/test/**/test.html';
    var testFiles   = root + '/taoBooklet/views/js/test/**/test.js';

    //extract unit tests
    var extractTests = function extractTests(){
        return grunt.file.expand([testRunners]).map(function(path){
            return path.replace(root, testUrl);
        });
    };

    grunt.config.merge({
        qunit: {
            taobooklet : {
                options: {
                    console : true,
                    urls : extractTests()
                }
            }
        },
        watch: {
            taobooklettest : {
                files : [testRunners, testFiles],
                tasks : ['qunit:taobooklet'],
                options : {
                    debounceDelay : 10000
                }
            }
        }
    });

    // register main test task
    grunt.registerTask('taobooklettest', ['qunit:taobooklet']);
};
