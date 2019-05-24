<?php defined('SHIELDON_VIEW') || exit('Life is short, why are you wasting time?');

$css = <<< EOF

<style>

    :selection { background-color: #E13300; color: white; }
    ::-moz-selection { background-color: #E13300; color: white; }

    body {
        background-color: #fff;
        margin: 40px;
        font: 13px/20px normal Helvetica, Arial, sans-serif;
        color: #4F5155;
    }

    a {
        color: #003399;
        background-color: transparent;
        font-weight: normal;
    }

    h1 {
        color: #444;
        background-color: transparent;
        border-bottom: 1px solid #D0D0D0;
        font-size: 19px;
        font-weight: normal;
        margin: 0 0 14px 0;
        padding: 14px 15px 10px 15px;
    }

    code {
        font-family: Consolas, Monaco, Courier New, Courier, monospace;
        font-size: 12px;
        background-color: #f9f9f9;
        border: 1px solid #D0D0D0;
        color: #002166;
        display: block;
        margin: 14px 0 14px 0;
        padding: 12px 10px 12px 10px;
    }

    #container {
        margin: 10px;
        border: 1px solid #D0D0D0;

    }

    fieldset {
        border: 1px solid #D0D0D0;
        margin: 10px;
    }

    p {
        margin: 12px 15px 12px 15px;
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

    a {
        text-decoration: none;
        color: #0069ff;
    } 

    .credit {
        padding: 0 10px 10px 10px;
        font-size: 12px;
    }

    .protection-icon {
        padding: 10px;
        text-align: center;
    }

    .protection-icon img {
        width: 250px;
        height: 250px;
    }

</style>

EOF;

return $css;