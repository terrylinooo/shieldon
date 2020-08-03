window._ = require('lodash');

try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) {}

require('@fortawesome/fontawesome-free/js/all.min.js');
require('datatables.net');
require('datatables.net-responsive');

window.ApexCharts = require('apexcharts');
