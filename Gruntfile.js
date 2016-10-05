/* jshint node:true */
module.exports = function (grunt) {
    // determine what else does Grunt need to do
    // other than build a deployable plugin
    var path = require('path'),
        fs = require( 'fs' ),
        version = getVersion(),
        BUILD_DIR_VER = 'build/' + version + '/',
        BUILD_DIR = BUILD_DIR_VER + 'mangapress-next/';

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        pot: {
            options: {
                dest: 'languages/',
                text_domain : '<%=pkg.name%>',
                keywords: ['__','_e','_n','_x','esc_html_e'] // functions to look for
            },
            files: {
                src:  [
                    '!tests/*',
                    '!bin/*',
                    '!node_modules/*',
                    '**/*.php'
                ], //Parse all php files
                expand: true
            }
        },
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
                        dest: BUILD_DIR
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
            'external-http': {
                cmd: 'phpunit',
                args: ['-c', 'phpunit.xml.dist', '--group', 'external-http']
            }
        },
        watch: {
            scripts: {
                files: ['**/*.php'],
                tasks: ['phpunit'],
                options: {
                    spawn: false
                }
            }
        },
        compress: {
            main: {
                options: {
                    archive: BUILD_DIR_VER + 'mangapress-next.zip'
                },
                files: [
                    {
                        expand: true,
                        cwd: BUILD_DIR,
                        src: ['**'],
                        dest: 'mangapress-next',
                        filter: 'isFile'
                    }
                ]
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

    grunt.registerTask('build', ['phpunit', 'update-readmes', 'copy', 'compress']);
    grunt.registerTask('update-readmes', updateReadmes);

    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-pot');

    /**
     * Reads the main plugin file and returns a version number
     * @returns {string}
     */
    function getVersion() {
        var pluginInfo = fs.readFileSync('mangapress-next.php', 'utf8'),
            pluginHeaders = {
                'Version' : ""
            },
            e = pluginInfo.substr(0, 8196).replace("\r", "\n");

        for (var regex in pluginHeaders) {
            var regString = '^[ \t\/*#@]*\/var/\:(.*)$'.replace('/var/', regex),
                reg = new RegExp(regString, 'mi'),
                matches = e.match(reg);

            pluginHeaders[regex] = matches[1].trim();
        }

        return pluginHeaders.Version;
    }


    /**
     * Update readme.txt with new version number
     * @todo Add comparison for version numbers to determine if update is necessary
     * @todo Find a way to use version number from package.json to keep plugin and readmes updated`
     */
    function updateReadmes() {
        var version = getVersion(),
            readMeTxt = fs.readFileSync('readme.txt', 'utf8'),
            regString = '^[ \t\/*#@]*\Stable tag\:(.*)$',
            reg = new RegExp(regString, 'mi'),
            matches = readMeTxt.match(reg),
            newStable = matches[0].replace(/:(.*)$/mi, ': ' + version),
            newReadmeTxt = readMeTxt.replace(matches[0], newStable);

        fs.writeFileSync('readme.txt', newReadmeTxt, 'utf8');
        console.info('readme.txt updated!');
    }
};