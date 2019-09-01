<?php

use Tests\XmlBuilder;

define('RESOURCE_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'res');
define('XML_MIN_VERSION', [3, 0, 0, 0]);
define('XML_UNENCRYPTED', RESOURCE_DIR . DIRECTORY_SEPARATOR . 'syspass.xml');
define('XML_ENCRYPTED', RESOURCE_DIR . DIRECTORY_SEPARATOR . 'syspass_encrypted.xml');
define('XML_SCHEMA', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'schemas' . DIRECTORY_SEPARATOR . 'syspass.xsd');

if (!file_exists(XML_UNENCRYPTED)) {
    try {
        $xmlBuilder = new XmlBuilder(XML_UNENCRYPTED);
        $xmlBuilder->run();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

if (!file_exists(XML_ENCRYPTED)) {
    try {
        $xmlBuilder = new XmlBuilder(XML_ENCRYPTED, true);
        $xmlBuilder->run();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
