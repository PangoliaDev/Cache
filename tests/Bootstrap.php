<?php

namespace Pangolia\CacheTests;

define( 'PANGOLIA_PHPUNIT', true );
define( 'PANGOLIA_DIR', __DIR__ );
define( 'PANGOLIA_FILE', __FILE__ );
define( 'WP_CONTENT_DIR', PANGOLIA_DIR);
define( 'WP_DEBUG_DISPLAY', true );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', false );

$composer = require __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/unit/CacheTestCase.php';