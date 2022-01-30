window._ = require('lodash');

try {
    window.Popper = require('@popperjs/core').default;
    window.$ = window.jQuery = require('jquery');

    window.bootstrap = require('bootstrap');
    require('jquery-resizable-columns');
    require('bootstrap-table');
    require('./node_modules/bootstrap-select/1.14.0_bootstrap-select.js');
    require('bootstrap-table/dist/extensions/resizable/bootstrap-table-resizable.min');
    require('bootstrap-table/dist/extensions/mobile/bootstrap-table-mobile.min');
    require('bootstrap-table/dist/extensions/cookie/bootstrap-table-cookie.min');
    require('bootstrap-table/dist/extensions/sticky-header/bootstrap-table-sticky-header.min');
} catch (e) {}

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

