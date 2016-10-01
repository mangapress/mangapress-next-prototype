/* jshint node:true */
module.exports = function (grunt) {
    // determine what else does Grunt need to do
    // other than build a deployable plugin
    var path = require('path'),
        fs = require( 'fs' ),
        BUILD_DIR = 'build/',
        version = getVersion(),
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
                        dest: BUILD_DIR + version + '/'
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

    grunt.registerTask('build', ['phpunit', 'copy']);

    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-watch');

    /**
     * Reads the main plugin file and returns a version number
     * @returns {string}
     */
    function getVersion() {
        var css = fs.readFileSync('mangapress-next.php', 'utf8'),
            pluginHeaders = {
                'Version' : ""
            },
            e = css.substr(0, 8196).replace("\r", "\n");
        for (var regex in pluginHeaders) {
            var regString = '^[ \t\/*#@]*\/var/\:(.*)$'.replace('/var/', regex),
                reg = new RegExp(regString, 'mi'),
                matches = e.match(reg);

            pluginHeaders[regex] = matches[1].trim();
        }

        return pluginHeaders.Version;
    }
};