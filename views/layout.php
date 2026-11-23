<?php
/** @var string $title */
/** @var string $description */
/** @var string $bodyClass */
/** @var string $contentView */
$isJsRequired = $contentView === 'javascript-required';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title) ?></title>
<?php if ($description !== ''): ?>
<meta name="description" content="<?= e($description) ?>">
<?php endif; ?>
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#0c1024">
<meta property="og:title" content="<?= e($title) ?>">
<?php if ($description !== ''): ?>
<meta property="og:description" content="<?= e($description) ?>">
<?php endif; ?>
<meta property="og:type" content="website">
<link rel="icon" href="/favicon.svg?v=3" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans+Arabic:wght@400;500;600&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/site.css">
<script>
(function () {
  function cookie(name) {
    var parts = document.cookie ? document.cookie.split("; ") : [];
    for (var i = 0; i < parts.length; i++) {
      var pair = parts[i].split("=");
      if (pair[0] === name) return decodeURIComponent(pair.slice(1).join("="));
    }
    return "";
  }
  try {
    if (cookie("adil-consent")) document.documentElement.classList.add("cookies-set");
    var theme = localStorage.getItem("theme");
    var lang = localStorage.getItem("lang");
    if (cookie("adil-consent") === "all") {
      if (cookie("adil-theme") === "light" || cookie("adil-theme") === "dark") theme = cookie("adil-theme");
      if (cookie("adil-lang")) lang = cookie("adil-lang");
    }
    document.documentElement.dataset.theme = theme === "light" ? "light" : "dark";
    if (lang && lang !== "en") {
      document.documentElement.lang = lang;
      document.documentElement.dir = lang === "ar" ? "rtl" : "ltr";
      document.documentElement.dataset.lang = lang;
      setTimeout(function () { document.documentElement.classList.add("i18n-ready"); }, 2500);
    }
  } catch (e) {
    document.documentElement.dataset.theme = "dark";
  }
})();
</script>
<?php if (!$isJsRequired): ?>
<noscript><meta http-equiv="refresh" content="0;url=/javascript-required/"></noscript>
<?php else: ?>
<script>location.replace("/");</script>
<?php endif; ?>
</head>
<body<?= $bodyClass !== '' ? ' class="' . e($bodyClass) . '"' : '' ?>>
<?php require view_path($contentView); ?>
<?php if (!$isJsRequired): ?>
<script src="/js/ar.js"></script>
<script src="/js/langs.js"></script>
<script src="/js/site.js"></script>
<?php endif; ?>
</body>
</html>
