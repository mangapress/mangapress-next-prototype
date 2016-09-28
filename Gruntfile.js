/* jshint node:true */
module.exports = function (grunt) {
    // determine what else does Grunt need to do
    // other than build a deployable plugin
    var path = require('path'),
        fs = require( 'fs' ),
        SOURCE_DIR = 'src/',
        BUILD_DIR = 'build/',
        autoprefixer = require('autoprefixer');

    grunt.initConfig({
        copy: {
            files: {
                files: [
                    {
                        // dot: true,
                        // expand: true,
                        // cwd: SOURCE_DIR,
                        src: [
                            '**',
                            '!**/.{svn,git}/**', // Ignore version control directories.
                            '!build/**',
                            '!bin/**',
                            '!node_modules/**',
                            '!tests/**',
                            '!Gruntfile.js',
                            '!package.json',
                            '!phpunit.xml.dist'
                        ],
                        dest: 'build/'
                    }
                ]
            }
        },
        phpunit: {
            'default': {
                cmd: 'phpunit',
                args: ['-c', 'phpunit.xml.dist']
            },
            ajax: {
                cmd: 'phpunit',
                args: ['-c', 'phpunit.xml.dist', '--group', 'ajax']
            },
            multisite: {
                cmd: 'phpunit',
                args: ['-c', 'tests/phpunit/multisite.xml']
            },
            'external-http': {
                cmd: 'phpunit',
                args: ['-c', 'phpunit.xml.dist', '--group', 'external-http']
            }
        }
    });

    // Testing tasks.
    grunt.registerMultiTask('phpunit', 'Runs PHPUnit tests, including the ajax, external-http, and multisite tests.', function() {
        grunt.util.spawn({
            cmd: this.data.cmd,
            args: this.data.args,
            opts: {stdio: 'inherit'}
        }, this.async());
    });

    grunt.loadNpmTasks('grunt-contrib-copy');
};