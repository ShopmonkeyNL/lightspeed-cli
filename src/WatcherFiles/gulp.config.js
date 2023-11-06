module.exports = {
  app: {
    name: 'shopmonkey',
  },
  css: {
    sourcePaths: [
      './src/sass/*.scss'
    ],
    exportPath: './theme/assets/'
  },
  thirdParty: {
    sassOptions: {
      errLogToConsole: true,
      outputStyle: 'expanded'
    },
    uglifyCssOptions: {
      'maxLineLen': 312,
      'uglyComments': true
    }
  }
}