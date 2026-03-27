<meta charset="utf-8" />
<?php
    $appName = config('app.name', 'CXTS');
    $pageTitle = trim(($subtitle ?? 'Dashboard') . ' | ' . $appName);
    $metaDescription = $metaDescription ?? 'CXTS adalah platform service operations dan IT service management untuk ticketing, SLA, approval workflow, asset context, inspection follow-up, dan operational reporting.';
    $metaKeywords = $metaKeywords ?? 'CXTS, ITSM, service desk, ticketing system, SLA, approval workflow, asset management, inspection operations, GM Tekno';
?>
<title><?php echo e($pageTitle); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?php echo e($metaDescription); ?>" />
<meta name="author" content="GM Tekno" />
<meta name="keywords" content="<?php echo e($metaKeywords); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="robots" content="index, follow" />
<meta name="theme-color" content="#ffffff">
<meta property="og:type" content="website" />
<meta property="og:site_name" content="<?php echo e($appName); ?>" />
<meta property="og:title" content="<?php echo e($pageTitle); ?>" />
<meta property="og:description" content="<?php echo e($metaDescription); ?>" />
<meta property="og:url" content="<?php echo e(url()->current()); ?>" />
<meta name="twitter:card" content="summary" />
<meta name="twitter:title" content="<?php echo e($pageTitle); ?>" />
<meta name="twitter:description" content="<?php echo e($metaDescription); ?>" />

<!-- App favicon -->
<link rel="shortcut icon" href="/images/favicon.ico">
<?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/layouts/partials/title-meta.blade.php ENDPATH**/ ?>