<?php defined('SHIELDON_VIEW') || exit('Life is short, why are you wasting time?');


$css = <<< EOF

    :selection, ::-moz-selection {
        background-color: #E13300;
        color: white;
    }

    html, body, .wrapper {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        background-color: {$ui['bg_color']};
        background-image: url({$ui['background_image']});
        font-family: "-apple-system", "BlinkMacSystemFont", "Helvetica Neue", "Helvetica", "Arial", "Segoe UI", "Oxygen", "Ubuntu", "Cantarell", "Open Sans", sans-serif;
    }

    .wrapper {
        position: absolute;
        text-align: center;
        display: flex;
        align-items: center;
        font-size: 14px;

    }

    .inner {
        text-align: center;
        width: 100%;
        max-width: 420px;
        padding-right: 15px;
        padding-left: 15px;
        margin-right: auto;
        margin-left: auto;
    }

    .inner .card {
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        word-wrap: break-word;
        background-color: #fff;
        background-clip: border-box;
        border: 1px solid rgba(0, 0, 0, 0.125);
        border-radius: 5px;
        box-shadow: 1px 4px 14px 1px rgba(0, 0, 0, {$ui['shadow_opacity']});
    }

    .inner .card .card-header {
        background-color: {$ui['header_bg_color']};
        color: {$ui['header_color']};
        font-weight: bold;
        padding: 0.75rem 1.25rem;
        margin-bottom: 0;
        border-radius: 4px 4px 0 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    .inner .card:before {
        content: "";
        position: absolute;
        background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAD4AAAAhCAYAAACBQRgKAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA+ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTQyIDc5LjE2MDkyNCwgMjAxNy8wNy8xMy0wMTowNjozOSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ0MgKFdpbmRvd3MpIiB4bXA6Q3JlYXRlRGF0ZT0iMjAxOS0xMC0xMFQxMTo1MDo1NSswODowMCIgeG1wOk1vZGlmeURhdGU9IjIwMTktMTAtMTBUMTE6NTY6MzErMDg6MDAiIHhtcDpNZXRhZGF0YURhdGU9IjIwMTktMTAtMTBUMTE6NTY6MzErMDg6MDAiIGRjOmZvcm1hdD0iaW1hZ2UvcG5nIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkYxQ0ZFMTMzRUIxMTExRTk4OThGQ0E5QzRBRjc1MTdEIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkYxQ0ZFMTM0RUIxMTExRTk4OThGQ0E5QzRBRjc1MTdEIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6RjFDRkUxMzFFQjExMTFFOTg5OEZDQTlDNEFGNzUxN0QiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6RjFDRkUxMzJFQjExMTFFOTg5OEZDQTlDNEFGNzUxN0QiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz4syRnsAAABFklEQVR42uTY2wqDMAwG4A58j73/0znnoWoPWYUIhW2i0mqSBnLjqXwU+UkfAPBU5ZQL3YTuqkLAPvQbG5YL0uELssVd9vENyfA+9Cu0/XVTIlyHrkObrYckwSfc4XHPwxLgBsHDkZc4w+0aTWde5gj3CG7XaJIO/xtNkuEd/scu1QepwwcEm9QfpgofETzlWoAafEawzr0QFbhFcH/VgnfDXZTFcOXCd8EBR8TmavBdcMDdbVJGE3X45pgoEa4RPFOKj5zwQ2OiBPipMZEz3CG4UwwqBfzrBFM6POmYyAW+ZrFVTOsoPNuYSBW+RFOdc0ykBjcI1kpYVRtj4ukTTI7wJCeYnOAQZbFXBVQVZbFTBdVHgAEANPFtoJ7uiSoAAAAASUVORK5CYII=');
        background-repeat: no-repeat;
        background-position: -2px -2px;
        width: 62px;
        height: 33px;
        border-top-left-radius: 4px;
        top: 0;
        left: 0;
        display: inline-block;
    }

    .status-container {
        display: flex;
        align-items: center;
    }

    .captcha-container {
        text-align: center;
    }

    .status-icon {
        width: 100px;
        height: 100px;
        padding: 10px;
        display: flex;
        justify-items: center;
        align-items: center;

    }

    .status-icon > img {
        width: 80px;
        height: 80px;
    }

    .status-info {
        text-align: center;
        margin-bottom: 30px;
    }

    .status-message {
        text-align: left;
        padding: 15px;
    }

    .status-user-info {
        padding: 10px;
        text-align: left;
        font-size: 12px;
        color: #999;
        line-height: 150%;
    }

    .status-user-info > .row {
        display: flex;
    }

    .status-user-info > .row > strong {
        color: #666;
        margin-right: 5px;
        width: 80px;
        display: inline-block;
    }

    .status-user-info > .row > span {
        display: inline-block;
        word-break: break-all;
        width: calc(100% - 80px);
    }

    a {
        color: #0069ff;
        background-color: transparent;
        font-weight: normal;
        text-decoration: none;
    }

    input[type=submit],
    input[type=button] {
        background-color: #eff3f6;
        background-image: linear-gradient(-180deg,#fafbfc,#eff3f6 90%);
        color: #24292e;
        -moz-appearance: none;
        -moz-user-select: none;
        -ms-user-select: none;
        -webkit-appearance: none;
        -webkit-user-select: none;
        appearance: none;
        background-position: -1px -1px;
        background-repeat: repeat-x;
        background-size: 110% 110%;
        border: 1px solid rgba(27,31,35,.2);
        border-radius: .25em;
        cursor: pointer;
        display: inline-block;
        font-size: 14px;
        font-weight: 600;
        line-height: 20px;
        padding: 6px 12px;
        position: relative;
        user-select: none;
        vertical-align: middle;
        white-space: nowrap;
    }

EOF;

return $css;