module.exports = function(grunt) {

   
    grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    concat: {
        dist: {
        src: [            
            'js/main.js',
            'js/us.widgets.js'  
        ],
        dest: 'js/production.js',
        }
    },
    uglify: {
        build: {
        src: 'js/main.js',
        dest: 'js/main.min.js'
        }
    },
    // compass: {
    //         dist: {
    //             files: {
    //                 'css/style.css' : 'sass/style.scss'
    //             }
    //         }
    //     },  
    less: {
      development: {
        options: {
          paths: ["less/"]
        },
        files: {
            "css/header.css": "less/header.less",
            "css/content.css": "less/content.less",
            "css/footer.css": "less/footer.less",
        }
      },
      production: {
        options: {
          paths: ["less/"],
          
          // modifyVars: {
          //   imgPath: '"http://mycdn.com/path/to/images"',
          //   bgColor: 'red'
          // }
        },
        files: {
            "css/header.css": "less/header.less",
            "css/content.css": "less/content.less",
            "css/footer.css": "less/footer.less",
        }
      }
    },
    // sprite:{
    //     all: {
    //         src: 'icons/header/*.png',
    //         dest: 'Avada-Child-Theme/sprites/header.png',
    //         destCss: 'css/header_sprite.css'
    //     },
    //     all: {
    //         src: 'icons/service/*.png',
    //         dest: 'Avada-Child-Theme/sprites/service.png',
    //         destCss: 'css/service_sprite.css'
    //     }
    // },
    // imagemin: {
    //     dynamic: {
    //         files: [{
    //             expand: true,
    //             cwd: 'images/',
    //             src: ['**/*.{png,jpg,gif}'],
    //             dest: 'images/build/'
    //         }]
    //     }
    // },
    cssmin: {
      	options: {
        	shorthandCompacting: false,
        	roundingPrecision: -1
        },
      	target: {
        		files: {
    			    'childstyle.css': ['css/normalize.css', 'css/header.css', 'css/content.css', 'css/footer.css' ]
        		}
     	}
    },
    watch: { 
        scripts:{ 
            files: ['**/*.js'],
            tasks: ['concat', 'uglify']
            
        },
        css:{
            // files: [],
            // tasks: [], 
            files: ['**/*.less', 'css/*.css'],
            tasks: ['less', 'cssmin']
        }
        // image:{
        //     files: ['**/*.{png,jpg,gif}'],
        //     tasks: ['imagemin']
        // }

    }
    
});
    grunt.loadNpmTasks('grunt-contrib-concat');
    // grunt.loadNpmTasks('grunt-contrib-compass');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-spritesmith');   
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-contrib-watch'); 
    grunt.registerTask('default', ['less', 'cssmin', 'imagemin']);   

};

    
