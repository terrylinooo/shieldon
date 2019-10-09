<?php defined('SHIELDON_VIEW') || exit('Life is short, why are you wasting time?');
/*
 * This file is part of the Shieldon package.
 *
 * (c) Terry L. <contact@terryl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Shieldon\Helper\_e;

$imgsrc = <<< EOF
data:image/svg+xml;base64,
PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwI
DAgNjQgNjQiIGhlaWdodD0iNjRweCIgdmVyc2lvbj0iMS4xIiB2aWV3Qm94PSIwIDAgNj
QgNjQiIHdpZHRoPSI2NHB4IiB4bWw6c3BhY2U9InByZXNlcnZlIiB4bWxucz0iaHR0cDo
vL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9y
Zy8xOTk5L3hsaW5rIj48ZyBpZD0iTGF5ZXJfMSI+PGc+PGNpcmNsZSBjeD0iMzIiIGN5P
SIzMiIgZmlsbD0iI0M3NUM1QyIgcj0iMzIiLz48L2c+PGcgb3BhY2l0eT0iMC4yIj48cG
F0aCBkPSJNMTYuOTU0LDUwYy00LjQsMC02LjItMy4xMTgtNC02LjkyOEwyOCwxNy4wMTJ
jMi4yLTMuODExLDUuOC0zLjgxMSw4LDBsMTUuMDQ2LDI2LjA2ICAgIGMyLjIsMy44MTEs
MC40LDYuOTI4LTQsNi45MjhIMTYuOTU0eiIgZmlsbD0iIzIzMUYyMCIvPjwvZz48Zz48c
GF0aCBkPSJNMTYuOTU0LDQ4Yy00LjQsMC02LjItMy4xMTgtNC02LjkyOEwyOCwxNS4wMT
JjMi4yLTMuODExLDUuOC0zLjgxMSw4LDBsMTUuMDQ2LDI2LjA2ICAgIGMyLjIsMy44MTE
sMC40LDYuOTI4LTQsNi45MjhIMTYuOTU0eiIgZmlsbD0iI0Y1Q0Y4NyIvPjwvZz48Zz48
cGF0aCBkPSJNMzQsMzJjMCwxLjEwNS0wLjg5NSwyLTIsMmwwLDBjLTEuMTA1LDAtMi0wL
jg5NS0yLTJ2LThjMC0xLjEwNSwwLjg5NS0yLDItMmwwLDBjMS4xMDUsMCwyLDAuODk1LD
IsMlYzMnogICAgIiBmaWxsPSIjNEY1RDczIi8+PC9nPjxnPjxwYXRoIGQ9Ik0zNCw0MGM
wLDEuMTA1LTAuODk1LDItMiwybDAsMGMtMS4xMDUsMC0yLTAuODk1LTItMmwwLDBjMC0x
LjEwNSwwLjg5NS0yLDItMmwwLDAgICAgQzMzLjEwNSwzOCwzNCwzOC44OTUsMzQsNDBMM
zQsNDB6IiBmaWxsPSIjNEY1RDczIi8+PC9nPjwvZz48ZyBpZD0iTGF5ZXJfMiIvPjwvc3
ZnPg==
EOF;

?>
<!DOCTYPE html>
<html lang="<?php echo $langCode ?>">
<head>
    <meta charset="utf-8">
    <meta name="robots" CONTENT="noindex, nofollow">
    <title><?php _e('core', 'deny.title') ?></title>
    <?php echo '<style>' . $css . '</style>'; ?>
</head>
<body>
	<div class="so-container">
		<h1><?= $lang['deny.heading'] ?></h1>
        <fieldset>
            <legend><?php _e('core', 'deny.message') ?></legend>
            <div class="so-icon">
                <img src="<?php echo $imgsrc; ?>">
            </div>
        </fieldset>
        <?php if ($showCreditLink) : ?>
            <div class="so-credit">
                <?php _e('core', 'credit', '', ['<a href="https://github.com/terrylinooo/shieldon" target="_blank">Shieldon</a>']); ?>
            </div>
        <?php endif; ?>
	</div>
</body>
</html>