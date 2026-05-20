const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = (env, argv) => {
    const isProduction = argv.mode === "production";

    return {
        entry: {
            main: path.resolve(__dirname, "js-src", "index.js"),
            admin: path.resolve(__dirname, "js-src", "admin.js"),
            "editor-style": path.resolve(__dirname, "scss", "editor-style.scss"),
        },
        output: {
            path: path.resolve(__dirname),
            filename: "js/[name].js",
            clean: false,
        },
        module: {
            rules: [
                {
                    test: /\.js$/u,
                    exclude: /node_modules/u,
                    use: {
                        loader: "babel-loader",
                    },
                },
                {
                    test: /\.s?css$/u,
                    use: [
                        MiniCssExtractPlugin.loader,
                        {
                            loader: "css-loader",
                            options: {
                                sourceMap: !isProduction,
                            },
                        },
                        {
                            loader: "postcss-loader",
                            options: {
                                sourceMap: !isProduction,
                            },
                        },
                        {
                            loader: "sass-loader",
                            options: {
                                sourceMap: !isProduction,
                            },
                        },
                    ],
                },
            ],
        },
        plugins: [
            new MiniCssExtractPlugin({
                filename: ({ chunk }) =>
                    chunk.name === "editor-style" ? "css/editor-style.css" : "css/style.css",
            }),
        ],
        resolve: {
            extensions: [".js", ".scss"],
        },
        target: "web",
        devtool: isProduction ? false : "source-map",
        stats: "minimal",
    };
};
