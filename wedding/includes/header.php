<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? "Our Wedding"); ?> | Alex &amp; Jordan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/assets/css/wedding-theme.css">
</head>
<body>

<!-- Jewel shimmer bar -->
<div class="jewel-bar"></div>

<header class="wedding-header">
    <div class="wedding-header-inner">

        <a href="/wedding/" class="wedding-site-title">
            <span class="lang-en active">Alex &amp; Jordan &nbsp;·&nbsp; June 14, 2026</span>
            <span class="lang-es">Alex &amp; Jordan &nbsp;·&nbsp; 14 de Junio, 2026</span>
        </a>

        <nav class="wedding-navbar" aria-label="Wedding navigation">
            <a href="/wedding/"
               class="<?php echo (basename($_SERVER['PHP_SELF']) === 'index.php' && dirname($_SERVER['PHP_SELF']) === '/wedding') ? 'active' : ''; ?>">
                <span class="lang-en active">Home</span>
                <span class="lang-es">Inicio</span>
            </a>
            <a href="/wedding/timeline.php"
               class="<?php echo (basename($_SERVER['PHP_SELF']) === 'timeline.php') ? 'active' : ''; ?>">
                <span class="lang-en active">Timeline</span>
                <span class="lang-es">Programa</span>
            </a>
            <a href="/wedding/activities.php"
               class="<?php echo (basename($_SERVER['PHP_SELF']) === 'activities.php') ? 'active' : ''; ?>">
                <span class="lang-en active">Activities</span>
                <span class="lang-es">Actividades</span>
            </a>
            <a href="/wedding/accommodations.php"
               class="<?php echo (basename($_SERVER['PHP_SELF']) === 'accommodations.php') ? 'active' : ''; ?>">
                <span class="lang-en active">Stay</span>
                <span class="lang-es">Hospedaje</span>
            </a>
            <a href="/wedding/party.php"
               class="<?php echo (basename($_SERVER['PHP_SELF']) === 'party.php') ? 'active' : ''; ?>">
                <span class="lang-en active">Wedding Party</span>
                <span class="lang-es">Cortejo</span>
            </a>
            <a href="/wedding/directions.php"
               class="<?php echo (basename($_SERVER['PHP_SELF']) === 'directions.php') ? 'active' : ''; ?>">
                <span class="lang-en active">Directions</span>
                <span class="lang-es">Cómo Llegar</span>
            </a>
            <a href="/wedding/rsvp.php"
               class="<?php echo (basename($_SERVER['PHP_SELF']) === 'rsvp.php') ? 'active' : ''; ?>">
                RSVP
            </a>
            <a href="/wedding/registry.php"
               class="<?php echo (basename($_SERVER['PHP_SELF']) === 'registry.php') ? 'active' : ''; ?>">
                <span class="lang-en active">Registry</span>
                <span class="lang-es">Lista de Bodas</span>
            </a>
            <a href="/wedding/songs.php"
               class="<?php echo (basename($_SERVER['PHP_SELF']) === 'songs.php') ? 'active' : ''; ?>">
                <span class="lang-en active">Songs</span>
                <span class="lang-es">Canciones</span>
            </a>

            <!-- Language Toggle -->
            <button id="langToggleBtn" class="lang-toggle" data-lang="en" aria-label="Switch to Spanish">
                <span class="lang-flag">🇪🇸</span> Español
            </button>
        </nav>

    </div>
</header>

<main>
