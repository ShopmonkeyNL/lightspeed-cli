const gulp = require('gulp');
const watch = require('gulp-watch');
const exec = require('gulp-exec');
const sass = require('gulp-sass')(require('sass'));

const watcher = watch(['layouts/*.rain', 'pages/*.rain', 'snippets/*.rain', 'assets/*', 'src/**/*']);

gulp.task('watch', function () {
    var path = '';
    watcher.on('change', function (path, stats) {
        if (path.endsWith('.scss')) {
            gulp.parallel('sass')(path);
        } else {
            return gulp.src('/')
                .pipe(exec('php ".functions/filewatcher.php" ' + '"' + path + '" change'))
                .pipe(exec.reporter());
        }
    });
    watcher.on('add', function (path, stats) {
        return gulp.src('/')
            .pipe(exec('php ".functions/filewatcher.php" ' + '"' + path + '" add'))
            .pipe(exec.reporter());
    });
    watcher.on('unlink', function (path, stats) {
        return gulp.src('/')
            .pipe(exec('php ".functions/filewatcher.php" ' + '"' + path + '" unlink'))
            .pipe(exec.reporter());
    });
});

gulp.task('sass', () => {
    return gulp.src('src/sass/**/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest('./assets/'));
});