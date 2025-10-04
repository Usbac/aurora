const path = require('path');

module.exports = (env, argv) => {
  const is_prod = argv.mode === 'production';

  return {
    entry: './src/index.js',
    output: {
      path: path.resolve(__dirname, '../../public/assets/js'),
      filename: 'admin2.js',
      clean: false,
    },
    devtool: is_prod ? false : 'source-map',
    module: {
      rules: [
        {
          test: /\.(js|jsx)$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader',
            options: {
              presets: [ '@babel/preset-env', '@babel/preset-react' ]
            }
          }
        }
      ]
    },
    resolve: {
      extensions: ['.js', '.jsx']
    },
    mode: 'production'
  }
};
