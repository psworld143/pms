<?php
/**
 * PMS Shared Assets Index
 * Lists all available shared assets
 */

header('Content-Type: application/json');

$assets = [
    'css' => [
        'pms-styles.css' => [
            'path' => '/seait/pms/assets/css/pms-styles.css',
            'description' => 'Main PMS stylesheet with unified theming',
            'version' => '1.0.0'
        ]
    ],
    'js' => [
        'pms-scripts.js' => [
            'path' => '/seait/pms/assets/js/pms-scripts.js',
            'description' => 'Main PMS JavaScript with unified functionality',
            'version' => '1.0.0'
        ]
    ],
    'images' => [
        'README.md' => [
            'path' => '/seait/pms/assets/images/README.md',
            'description' => 'Image assets documentation',
            'type' => 'documentation'
        ]
    ],
    'fonts' => [
        'README.md' => [
            'path' => '/seait/pms/assets/fonts/README.md',
            'description' => 'Font assets documentation',
            'type' => 'documentation'
        ]
    ]
];

echo json_encode([
    'status' => 'success',
    'message' => 'PMS Shared Assets',
    'version' => '1.0.0',
    'assets' => $assets,
    'usage' => [
        'css' => 'Include in HTML head: <link rel="stylesheet" href="/seait/pms/assets/css/pms-styles.css">',
        'js' => 'Include before closing body: <script src="/seait/pms/assets/js/pms-scripts.js"></script>',
        'images' => 'Use relative paths: <img src="/seait/pms/assets/images/icon-dashboard.png">',
        'fonts' => 'Define in CSS: @font-face { font-family: "Inter"; src: url("/seait/pms/assets/fonts/Inter-Regular.woff2"); }'
    ]
]);
?>
