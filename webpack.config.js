const Encore = require('@terminal42/contao-build-tools');

module.exports = Encore('assets')
    .setOutputPath('public/')
    .setPublicPath('/bundles/cgoitfoldergallery')
    .addEntry('folder-gallery-js', './assets/js/folder-gallery.js')
    .addStyleEntry('folder-gallery-css', './assets/scss/folder-gallery.scss')
    .addStyleEntry('backend-css', './assets/scss/backend.scss')
    .getWebpackConfig()
;
