<?php

return [
    'allowed_extensions' => [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'pdf',
        'txt',
    ],

    'blocked_extensions' => [
        'php',
        'phtml',
        'phar',
        'php3',
        'php4',
        'php5',
        'phps',
        'pht',
        'svg',
        'htaccess',
    ],

    'max_size' => 5 * 1024 * 1024,

    'allowed_mime' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf',
        'text/plain',
    ],

    'rules' => [
        'extension' => true,
        'mime' => true,
        'magic_bytes' => true,
        'filename' => true,
        'size' => true,
        'content_scan' => true,
    ],

    'content_signatures' => [
        '<?php',
        '<?=',
        'eval(',
        'base64_decode',
        'system(',
        'shell_exec',
        'passthru',
        '<script',
    ],
];
