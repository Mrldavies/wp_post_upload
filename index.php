<?php

/**
 * Plugin Name: DocUpload
 * Plugin URI: https://example.com/plugins/doc-upload
 * Description: A WordPress plugin for uploading and converting Word documents to HTML.
 * Version: 1.0.0
 * Author: MrLdavies
 * Author URI: https://example.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace mrldavies\DocUpload;

require_once(__DIR__ . '/vendor/autoload.php');

$plugin = new DocUpload();
$plugin->run();
