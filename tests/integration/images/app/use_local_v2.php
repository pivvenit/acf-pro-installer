<?php
$composerVersion = shell_exec('composer --version');
if (preg_match('/version 2/', $composerVersion)) {
    copy("/composer.phar", "/usr/bin/composer");
}