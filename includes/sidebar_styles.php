<?php
$sidebarCssPath = function_exists('ycdo_resolve_app_path')
    ? ycdo_resolve_app_path('css/sidebar.css')
    : '/css/sidebar.css';
?>
<link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars($sidebarCssPath, ENT_QUOTES, 'UTF-8'); ?>?v=2">
