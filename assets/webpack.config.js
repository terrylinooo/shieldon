

const path = require('path');
const webpack = require('webpack');
const fs = require('fs');

/*
|--------------------------------------------------------------------------
| Webpack for the Shieldon firewall control panel.
|--------------------------------------------------------------------------
*/

// Save CSS files.
const miniCssExtractPlugin = require("mini-css-extract-plugin");

// Write the static version number into a file.
const eventHooksPlugin = require('event-hooks-webpack-plugin');

/*
|--------------------------------------------------------------------------
| The package list.
|--------------------------------------------------------------------------
*/

var mustRequiredModules = [
    "./src/scss/firewall-ui.scss",
    // "./plugins/jquery/jquery.min.js",
];

// Our default theme, maybe more themes in the furture...
var defaultTheme = mustRequiredModules.slice();
// defaultTheme.push("./themes/default/scss/main.scss");

/*
|--------------------------------------------------------------------------
| Start packaging...
|--------------------------------------------------------------------------
*/

module.exports = (env, argv) => ({

    externals: {
        //"jquery": "jQuery"
    },
    entry: {
        // Theme
        "default": defaultTheme,
    },
    output: {
        //path: path.resolve(__dirname, "./../public/js"),
        filename: "./dist/[name].packed.js"
    },
    module: {
        rules: [
            {
                // Js files only.
                test : /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: [
                    {
                        loader: 'script-loader',
                        options: {
                            sourceMap: true,
                        },
                    },
                ]
            },
            {
                // Image and font files only.
                test: /\.(ttf|eot|svg|otf|gif|png|jpg|woff|woff2)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
                loader: 'url-loader',
            },
            {
                // CSS, SASS, SCSS files only.
                test: /\.css|sass|scss$/,

                use: [{
                    loader: miniCssExtractPlugin.loader,
                }, {
                    loader: "css-loader",
                }, {
                    loader: "sass-loader",
                    options: {
                        implementation: require("sass"),
                    }
                }]
            }
        ]
    },
    plugins: [
        new miniCssExtractPlugin({
            filename: "./dist/[name].packed.css" 
        }),
        new eventHooksPlugin({
            done: () => {
                var dt = new Date();
                var DD = ('0' + dt.getDate()).slice(-2);
                var MM = ('0' + (dt.getMonth() + 1)).slice(-2);
                var YYYY = dt.getFullYear();
                var hh = ('0' + dt.getHours()).slice(-2);
                var mm = ('0' + dt.getMinutes()).slice(-2);
                var ss = ('0' + dt.getSeconds()).slice(-2);
                var dateString = YYYY + MM + DD + hh + mm  + ss;

                // DO NOT MODIFY.
                var phpContent = "<?php \n\n" + "return '" + dateString + "';\n";

                fs.writeFile('version.php', phpContent, function(err) {
            
                    if (err) {
                        return console.log(err);
                    }
                    console.log('[' + argv.mode + '] version number updated. (ver. ' + dateString + ')');
                }); 
            }
        })
    ]
});

