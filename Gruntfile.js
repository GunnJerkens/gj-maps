module.exports = function (grunt) {
  'use strict';

  // load all grunt tasks
  require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

  grunt.initConfig({

    pkg: grunt.file.readJSON('package.json'),

    concat: {
      min: {
        files: {
          'js/main.js': ['js/src/*.js']
        }
      }
    },

    compass: {
      dist: {
        options: {
          config: 'css/config.rb',
          sassDir: 'css/sass',
          imagesDir: 'img',
          cssDir: 'css',
          environment: 'production',
          outputStyle: 'compressed',
          force: true
        }
      }
    },

    watch: {
      options: {
        livereload: true
      },
      scripts: {
        files: ['js/src/*.js'],
        tasks: ['concat']
      },
      styles: {
        files: ['img/ui/*.png'],
        tasks: ['compass']
      },
    },
  });

  // Development task checks and concatenates JS, compiles SASS preserving comments and nesting, runs dev server, and starts watch
  grunt.registerTask('default', ['compass', 'concat', 'watch']);

};
