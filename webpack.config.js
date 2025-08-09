const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        'pos': path.resolve(process.cwd(), 'assets/js/src', 'pos.js'),
        'admin': path.resolve(process.cwd(), 'assets/js/src', 'admin.js'),
    },
    output: {
        path: path.resolve(process.cwd(), 'assets/js/dist'),
        filename: '[name].js',
    },
    resolve: {
        ...defaultConfig.resolve,
        alias: {
            ...defaultConfig.resolve.alias,
            '@': path.resolve(process.cwd(), 'assets/js/src'),
        },
    },
};