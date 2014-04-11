'use strict';

module.exports = function (grunt) {

  // load all grunt tasks
  require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

  grunt.initConfig({

    pkg: grunt.file.readJSON('package.json'),
 
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
          config: 'style/config.rb',
          sassDir: 'style/sass',
          imagesDir: 'img',
          cssDir: 'style',
          environment: 'production',
          outputStyle: 'compressed',
          force: true
        }
      }
    },

    imagemin: {
      dynamic: {
        files: [{
          expand: true,
          cwd: 'img/src',
          src: ['*.{png,jpg,gif}'],
          dest: 'img/'
        }]
      }
    },

    watch: {
      options: {
        livereload: true
      },
      scripts: {
        files: ['js/src/*.js'],
        tasks: ['uglify']
      },
      styles: {
        files: ['img/ui/*.png'],
        tasks: ['compass']
      },
      images: {
        files: ['img/src/*.{png,jpg,gif}'],
        tasks: ['imagemin']
      }
    },
  });
 
  // Development task checks and concatenates JS, compiles SASS preserving comments and nesting, runs dev server, and starts watch
  grunt.registerTask('default', ['compass', 'uglify', 'imagemin', 'watch']);
 
 }
