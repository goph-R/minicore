<?php

require_once 'core/Framework.php';

Framework::dispatch(
    ['YourApp', 'dev', 'config.ini.php'],
    ['core', 'app']
);
