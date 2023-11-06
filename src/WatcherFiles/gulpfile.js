const config = require('./gulp.config');
const concat = require('gulp-concat');
const autoprefixer = require('gulp-autoprefixer');
const del = require('del');
const gulp = require('gulp');
const watch = require('gulp-watch');
const sass = require('gulp-sass')(require('sass'));
const uglifycss = require('gulp-uglifycss');
const exec = require('gulp-exec');
const path = require('path');
const fs = require('fs');

const watcher = watch(['theme/layouts/*.rain', 'theme/pages/*.rain', 'theme/snippets/*.rain', 'theme/assets/*', 'src/**/*']);

gulp.task('watch', function () {
    let path = '';
    watcher.on('change', function (path, stats) {
        if (path.endsWith('.scss')) {
            gulp.parallel('sass')(path);
        }

        if (!path.includes('src')) {
            return gulp.src('/')
                .pipe(exec('php ".functions/filewatcher.php" ' + '"' + path + '" change'))
                .pipe(exec.reporter());
        }
    });
    watcher.on('add', function (path, stats) {
        if (!path.includes('src')) {
            return gulp.src('/')
                .pipe(exec('php ".functions/filewatcher.php" ' + '"' + path + '" add'))
                .pipe(exec.reporter());
        }
    });
    watcher.on('unlink', function (path, stats) {
        if (!path.includes('src')) {
            return gulp.src('/')
                .pipe(exec('php ".functions/filewatcher.php" ' + '"' + path + '" unlink'))
                .pipe(exec.reporter());
        }
    });
});

gulp.task('sass', () => {
    del(config.css.sourcePaths + '**/*', { force: true });

    return gulp.src(config.css.sourcePaths)
        .pipe(sass(config.thirdParty.sassOptions).on('error', sass.logError))
        .pipe(concat('bundle.css'))
        .pipe(autoprefixer())
        .pipe(uglifycss(config.thirdParty.uglifyCssOptions))
        // .pipe(rename({ suffix: '.min' })) current setup does not allow double file extensions
        .pipe(gulp.dest(config.css.exportPath))
});

gulp.task('raincs', () => {
    const altRegex = /<img\b(?![^>]*alt=)[^>]*>/;
    const currentDirectory = './theme';
    const filesAndDirs = fs.readdirSync(currentDirectory);
    const directories = filesAndDirs.filter(item => {
      return fs.statSync(path.join(currentDirectory, item)).isDirectory();
    });
    
    directories.forEach(directory => {
        const rainFiles = fs.readdirSync(path.join(currentDirectory, directory));

        rainFiles.forEach(file => {
            if (!file.endsWith('.rain') && !file.endsWith('.css.rain')) return;

            const content = fs.readFileSync(`${directory}/${file}`, 'utf-8').split('\n');

            content.forEach((line, index) => {
                const missingAlt = line.match(altRegex);
                if (missingAlt) {
                    console.log(`Missing alt attribute in file ${directory}/${file}:${index + 1}`);
                }
            })
        });
    });
});