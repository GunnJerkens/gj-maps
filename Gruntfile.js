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

    uglify: {
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
      }
    }
  });

  // Default runs concat and compass with live reload, build compiles for production
  grunt.registerTask('dev', ['compass', 'concat', 'watch']);
  grunt.registerTask('default', ['compass', 'uglify']);

};
