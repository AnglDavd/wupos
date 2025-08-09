const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        'pos': [
            path.resolve(process.cwd(), 'assets/js/src', 'pos.js'),
            path.resolve(process.cwd(), 'assets/css/src', 'pos.scss'),
        ],
        'admin': [
            path.resolve(process.cwd(), 'assets/js/src', 'admin.js'),
            path.resolve(process.cwd(), 'assets/css/src', 'admin.scss'),
        ],
    },
    output: {
        path: path.resolve(process.cwd(), 'assets'),
        filename: 'js/dist/[name].js',
    },
    resolve: {
        ...defaultConfig.resolve,
        alias: {
            ...defaultConfig.resolve.alias,
            '@': path.resolve(process.cwd(), 'assets/js/src'),
        },
    },
};