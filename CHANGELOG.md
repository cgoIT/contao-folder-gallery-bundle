# Changelog

## [1.2.2](https://github.com/cgoIT/contao-folder-gallery-bundle/compare/v1.2.1...v1.2.2) (2026-07-25)


### Bug Fixes

* add better responsive behaviour for gallery overview and content layouts ([01de9f1](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/01de9f1d1afd8429290da5f4005838175b6d18ba))

## [1.2.1](https://github.com/cgoIT/contao-folder-gallery-bundle/compare/v1.2.0...v1.2.1) (2026-07-25)


### Bug Fixes

* fix Argument [#4](https://github.com/cgoIT/contao-folder-gallery-bundle/issues/4) ($hideCoverInGallery) must be of type bool, null given in GalleryMetadataFactory ([0b9cb3b](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/0b9cb3b118f055f27031769682eb2508236f20ea))

## [1.2.0](https://github.com/cgoIT/contao-folder-gallery-bundle/compare/v1.1.0...v1.2.0) (2026-07-24)


### Features

* add gallery urls to generated sitemap ([8a524fa](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/8a524fa9a1221ded7c5117c0c1911849e2b798e7))
* optimize backend editor, add option to hide a cover image in gallery and use it only for the gallery overview ([2607cb0](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/2607cb0078b4ac8b495b37bdf2bae423177ff390))
* show information about selected node in backend editor ([ce34ece](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/ce34ecea1130ca5e5103ec4ca38babcb2c5bdacb))


### Bug Fixes

* add class `node--active` in backend folder for selected item ([56b95e6](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/56b95e60557e8af9c0c5c9a00e79a345e9cf973f))
* close file picker and show error message in case of an invalid cover image before saving the _metadata.yml ([73e9a15](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/73e9a15d29ae4096e4466e625e97697188da9039))
* map hideCoverInGallery to new property in backend dca ([25e62f3](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/25e62f3661728c9352c915ca31e2a965685e7ed5))


### Miscellaneous Chores

* fix linting error in GalleryMetadataFactory ([12979ef](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/12979ef8231973714932a71a5303e414a2552d2d))

## [1.1.0](https://github.com/cgoIT/contao-folder-gallery-bundle/compare/v1.0.0...v1.1.0) (2026-07-22)


### Features

* add custom maintenance task to purge the folder gallery cache ([8a5e91a](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/8a5e91a3bbcb7f4bbab4c6ad55be13d36b720dc3))
* add explanation and use enum instead of options_callback in tl_gallery_metadata dca ([ed500c0](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/ed500c0b5ee1effb9c35187658bab6d14349362b))


### Bug Fixes

* adapt some labels and descriptions in tl_module to better align with contao standards ([eb263fa](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/eb263fa117aee0caeb6e41cf4a3e2179c1061539))
* adapt some labels and descriptions in tl_module to better align with contao standards ([7ae6b5a](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/7ae6b5a5663e7078754458d99e1b589eb169c3e8))

## 1.0.0 (2026-07-21)


### Features

* add (s)css for folder gallery component ([e29741d](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/e29741de3a412c7d6f75c9d4269ce2964d9aba25))
* add all fields to editor ([8cc24aa](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/8cc24aa99780812b97ff5bbe1c8236b650d9f49f))
* add breadcrumbs and back button to gallery view ([823b7f9](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/823b7f92c4d4e6ea68cda969c76285ff8b1fcdf0))
* add breadcrumbs and back button to gallery view ([b28b0f2](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/b28b0f2b5b556b182345d0031887e866fc42d2e7))
* add cache invalidation based on filesystem events ([1def95c](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/1def95c0c929e04f7a40e6c33506f9da82d3503c))
* add caching support for gallery overview ([cf2a12f](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/cf2a12f69e508ceeae266cda7583414e3482744a))
* add filesystem fingerprint to cache key ([b89d023](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/b89d0232a1676c0dced7c110b43c4cfb5afc033c))
* add first draft of frontend module ([ae5c582](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/ae5c5827f1ad781a71b379565e7ad8c6911941bc))
* add first draft of gallery content ([9613c06](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/9613c06b1acf0d3a8e08d48227df9d2ca56c9585))
* add first draft of model and unit tests ([40f2e85](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/40f2e85a3b6a3d85833c29694c01acd92843e402))
* add first implementation of BE module ([90cb35d](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/90cb35d64f2e72f3e412c5561791589d12662b99))
* add grouping for overview mode ([9a84df9](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/9a84df9cca8c8a673ad0ce21e30d163e508ff64f))
* add ModelProvider, ImageLoader and Repository ([0ecb286](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/0ecb2864cb5f87d7eb2cfaeb69e69dd006c36535))
* add preparation for BE module to edit _metadata.yml files ([ba19034](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/ba19034a35e07b0aff6e69dee2ad8882fb806771))
* add support for default lightbox and photoswipe ([e4b2d1a](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/e4b2d1ad968a3456d5f46e4ad9bbd1201c90730e))
* add trail information to gallery folders ([a47c3ba](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/a47c3ba05843cf5051a885ae82b89ae995f18bbe))
* always sort folders by directory name in filesystem and not the title from metadata.yml ([453250c](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/453250c96ea62b7b5527dd3d826c1d35ddde83fe))
* better logging for _metadata.yml loader ([a6a2c84](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/a6a2c84f537a4cca4ae5a5093d83e31268f68584))
* create new backend controller and align more with TemplateStudio ([bd58972](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/bd58972ab066069f97915c953924e8d60f045c96))
* finish backend editor by implementing AJAX handling ([d8b2122](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/d8b21220ba6051ca85970515fb301597a6fff937))
* finish tree view ([8093c36](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/8093c36e2b9bbd3b115942546d45709e5b9cf50f))
* first implementation iteration of DC_GalleryMetadata ([11a7946](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/11a7946177cea3c30c4710bbce290c24be15c00f))
* introduce more metadata, e.g. overview_mode ([c3c7feb](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/c3c7febd6104731d99df1368952f9de570015338))
* introduce more metadata, e.g. overview_mode ([4565a12](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/4565a128cfcb7175fafc31906342b252d36c2b90))
* invalidate gallery cache when FolderGallery module is saved ([dba7de7](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/dba7de742ae01e81e0d1bba9e1c602ed44691df6))
* optimize tree view for backend module ([5971dc8](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/5971dc8f24245f3ab3bb15603b01dc86e7a02cc9))
* prepare reader functionality ([ff4233c](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/ff4233cb6e1349a3070bf8e864c9d9d37db3cfdb))
* refactor tests ([a800052](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/a800052891b548fc8226170a0a063fe29cfc9db1))
* refactor to more general data structure with folders and subfolders ([4743037](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/474303762802e1d24a2b70e77c3cbed29c477363))
* rendering logic is now in twig template and not in custom renderer ([53e5105](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/53e51051d91459f64bf185d51ae4c759e0894fe6))
* replace `GalleryCacheKeyGenerator` with `GalleryCache`, switch to tag-based caching. ([1e426ab](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/1e426ab3f61c541fa09f70bd122b9899aa7d57c4))
* save data in editor ([7ba83d2](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/7ba83d2fee0cab04ef75f177127987b2b8adf947))
* set path for fileTree widget when selecting cover image ([5824183](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/5824183b8f3efe85ecc5722e1f9a4ca61c3f3f82))
* show editor for selected metadata files ([cf4d604](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/cf4d604c166667a24b9b7fd24a412cadbf9d6209))
* show image and sub-gallery count in overview mode ([0edd726](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/0edd7265dd29d066b02c822a4059737901d3438a))
* show module name in backend editor ([6a8ca4f](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/6a8ca4fd2ffcf729fa305fd9baa6e68243ea6964))
* show page name as first entry in breadcrumbs ([92d5de4](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/92d5de4bbdc9f90ffe9354c2a4fab3148d95852d))
* split folder overview into component template ([ac4ff41](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/ac4ff411ee08fcd81fb009bf74e7ae387ddf2ce7))
* use different heading levels based on folder level in gallery overview ([6645975](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/6645975bdb65d517df7a78db559409e7e2feef91))
* use more general name for css ([3b50154](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/3b5015494765ee5d456cd6a0578530865e22b6c1))


### Bug Fixes

* add file header to all files, add more unit tests ([61a882d](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/61a882d9e24371d9ad371160fd04eac10c65a320))
* add missing translation for gallery_mode 'transparent' ([5d35f02](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/5d35f0279a6d4110fc0f3f191801b702018e4295))
* add yamllint to composer scripts ([e489a83](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/e489a833ea4ace9579bc9dd621268e3e1910a4b4))
* align headline levels ([f3e66f4](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/f3e66f4b20f96237358cfaac7016f11eec434a46))
* better description for cover image field ([02ab132](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/02ab132b8652ca1dd21eb4868fda60bda2eaf249))
* conditionally add cover image to dbafs if not already synced ([c8ac77b](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/c8ac77be17748ea5e63c105fc88160666f99dcf6))
* correct styling for overview and content ([2f62206](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/2f622065bcadd2a37dc3fee09e7609cc33f31aa4))
* correctly determine children and sub-gallery count in content view ([415c8d1](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/415c8d1c918796abcc3e4149e8a3779330215099))
* eliminate OverviewFolderFlattener ([c628fb4](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/c628fb4d12d21fc84e8975b91ad1360e0c957704))
* fix build-tools errors ([6e3d31e](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/6e3d31e72f8f6ffc751d5ab8d1082b461c3c4544))
* fix first module setup ([1146097](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/114609792765ee1257d1c7dcada49f9af4fa73f2))
* fix image size fields for frontend module ([2f28aae](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/2f28aae5896007811fbcaa5e27526544d7369650))
* fix stylelint errors ([675dc90](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/675dc9036cbf4a72e8b5f34d7b43312fd15dbb63))
* fix tests for filesystem fingerprint provider ([4c0679a](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/4c0679ae3b152dcf24884943b70b360220e4f436))
* load language file 'default' in backend module ([7c66f9a](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/7c66f9ac628c078d865a4c1a1ab40656074649ef))
* remove old css files ([3758c8a](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/3758c8a33f97c9d160ffaa0d43c1fb60cef852fd))


### Miscellaneous Chores

* fixes due to linting ([7a95017](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/7a95017113fcb15fae18520d0b1091a6605731db))
* fixes due to linting ([92d9e7f](https://github.com/cgoIT/contao-folder-gallery-bundle/commit/92d9e7f3269d2406cfa29732114afd3b1b1a8940))
