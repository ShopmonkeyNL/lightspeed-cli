const { watch, src, task } = require("gulp");
const exec = require("gulp-exec");

const watcher = watch([
  "theme/layouts/*.rain",
  "theme/pages/*.rain",
  "theme/snippets/*.rain",
  "theme/assets/*",
  "src/**/*",
]);
task("watch", () => {
  let path = "";
  watcher.on("change", (path, stats) => {
    if (!path.includes("src")) {
      return src("/")
        .pipe(exec(`php ".functions/filewatcher.php" "${path}" change`))
        .pipe(exec.reporter())
        .on("end", () => this.emit("end"));
    }
  });

  watcher.on("add", (path, stats) => {
    if (!path.includes("src")) {
      return src("/")
        .pipe(exec(`php ".functions/filewatcher.php" "${path}" add`))
        .pipe(exec.reporter())
        .on("end", () => this.emit("end"));
    }
  });

  watcher.on("unlink", (path, stats) => {
    if (!path.includes("src")) {
      return src("/")
        .pipe(exec(`php ".functions/filewatcher.php" "${path}" unlink`))
        .pipe(exec.reporter())
        .on("end", () => this.emit("end"));
    }
  });
});
