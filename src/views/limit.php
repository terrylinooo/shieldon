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
PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgZGF0YS1uYW1lPSJMYXllciAxIiBpZD0iT
GF5ZXJfMSIgdmlld0JveD0iMCAwIDE0MCAxNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm
9yZy8yMDAwL3N2ZyI+PGRlZnM+PHN0eWxlPi5jbHMtMXtmaWxsOiM1MmIxYjE7fS5jbHM
tMntmaWxsOiM1MjUzNTQ7fS5jbHMtM3tmaWxsOiNmZmY7fS5jbHMtNHtmaWxsOiNjY2Jl
YjA7fTwvc3R5bGU+PC9kZWZzPjx0aXRsZS8+PGNpcmNsZSBjbGFzcz0iY2xzLTEiIGN4P
SI3MCIgY3k9IjcwIiByPSI2NCIvPjxwYXRoIGNsYXNzPSJjbHMtMiIgZD0iTTk3LDM2Vj
I5YTEsMSwwLDAsMC0xLTFINDZhMSwxLDAsMCwwLTEsMXY3YTEsMSwwLDAsMCwxLDFoNWM
tMSwzLjEtMSw2Ljg0LTEsMTEsMCw3LjMsOC4zMSwxNi44OSwxNC40OCwyM0M1OC4zMSw3
Ny4xMSw1MCw4Ni43LDUwLDk0YzAsNC4xNiwwLDcuOSwxLDExSDQ2YTEsMSwwLDAsMC0xL
DF2N2ExLDEsMCwwLDAsMSwxSDk2YTEsMSwwLDAsMCwxLTF2LTdhMSwxLDAsMCwwLTEtMU
g5MWMxLTMuMSwxLTYuODQsMS0xMSwwLTcuMy04LjMxLTE2Ljg5LTE0LjQ4LTIzQzgzLjY
5LDY0Ljg5LDkyLDU1LjMsOTIsNDhjMC00LjE2LDAtNy45LTEtMTFoNUExLDEsMCwwLDAs
OTcsMzZaIi8+PHBhdGggY2xhc3M9ImNscy0zIiBkPSJNOTYsMzVWMjhhMSwxLDAsMCwwL
TEtMUg0NWExLDEsMCwwLDAtMSwxdjdhMSwxLDAsMCwwLDEsMWg1Yy0xLDMuMS0xLDYuOD
QtMSwxMSwwLDcuMyw4LjMxLDE2Ljg5LDE0LjQ4LDIzQzU3LjMxLDc2LjExLDQ5LDg1Ljc
sNDksOTNjMCw0LjE2LDAsNy45LDEsMTFINDVhMSwxLDAsMCwwLTEsMXY3YTEsMSwwLDAs
MCwxLDFIOTVhMSwxLDAsMCwwLDEtMXYtN2ExLDEsMCwwLDAtMS0xSDkwYzEtMy4xLDEtN
i44NCwxLTExLDAtNy4zLTguMzEtMTYuODktMTQuNDgtMjNDODIuNjksNjMuODksOTEsNT
QuMyw5MSw0N2MwLTQuMTYsMC03LjktMS0xMWg1QTEsMSwwLDAsMCw5NiwzNVoiLz48cGF
0aCBjbGFzcz0iY2xzLTEiIGQ9Ik04Ny4zNCw0N0M4Ny4zNCw1Ni41OSw3MCw3MSw3MCw3
MVM1Mi42Niw1Ni41OSw1Mi42Niw0Nyw1Mi42NiwzMC41LDcwLDMwLjUsODcuMzQsMzcuN
DQsODcuMzQsNDdaIi8+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNODcuMzQsOTNDODcuMz
QsODMuNDEsNzAsNjksNzAsNjlTNTIuNjYsODMuNDEsNTIuNjYsOTNzMCwxNi41MSwxNy4
zNCwxNi41MVM4Ny4zNCwxMDIuNTYsODcuMzQsOTNaIi8+PHBhdGggY2xhc3M9ImNscy00
IiBkPSJNODcuMTMsOTkuMDljMCwuMzEtLjA3LjYxLS4xMi45MS0uOSw1Ljc3LTQuMjksO
S41LTE3LDkuNXMtMTYuMTEtMy43My0xNy05LjVjMC0uMy0uMDktLjYtLjEyLS45MSwzLj
Q1LTcuNjgsMTMuOS0xNi4yNiwxNi40OC0xOC40OWExLDEsMCwwLDEsMS4zLDBDNzMuMjM
sODIuODMsODMuNjgsOTEuNDEsODcuMTMsOTkuMDlaIi8+PHBhdGggY2xhc3M9ImNscy00
IiBkPSJNODYuNTEsNTFjLTMsNy45LTEzLjE4LDE3LjA4LTE1Ljg2LDE5LjRhMSwxLDAsM
CwxLTEuMywwQzY2LjY3LDY4LjA4LDU2LjQ3LDU4LjksNTMuNDksNTFaIi8+PC9zdmc+
EOF;

?>
<!DOCTYPE html>
<html lang="<?php echo $langCode ?>">
<head>
    <meta charset="utf-8">
    <meta name="robots" CONTENT="noindex, nofollow">
    <title><?php _e('core', 'core', 'limit.title', 'Access denied!'); ?></title>
    <?php echo '<style>' . $css . '</style>'; ?>
</head>
<body>
	<div class="so-container">
		<h1><?php _e('core', 'limit.heading'); ?></h1>
        <fieldset>
            <legend><?php _e('core', 'limit.message', 'This page is limiting the number of people online. Please wait a moment.'); ?></legend>
            <div class="so-icon">
                <img src="<?php echo $imgsrc; ?>">
            </div>
            <?php if ($showLineupInformation || $showOnlineInformation) : ?>
                <div class="so-info">
                    <?php if ($showLineupInformation) : ?>
                        <?php _e('core', 'lineup_info', '', ['<strong>' . $this->currentWaitNumber . '</strong>']); ?>&nbsp;&nbsp;
                    <?php endif; ?>
                    <?php if ($showOnlineInformation) : ?>
                        <?php _e('core', 'online_info', '', ['<strong>' . $this->sessionCount . '</strong>']); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </fieldset>
        <?php if ($showCreditLink) : ?>
            <div class="so-credit">
                <?php _e('core', 'credit', '', ['<a href="https://github.com/terrylinooo/shieldon" target="_blank">Shieldon</a>']); ?></div>
        <?php endif; ?>
	</div>
</body>
</html>