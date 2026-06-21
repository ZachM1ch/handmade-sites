<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . " | Zachary & Alina's Wedding" : "Zachary & Alina's Wedding"; ?></title>
    <link rel="stylesheet" href="/assets/css/jacks-barn-theme.css">
</head>
<body>

<header>
    <nav class="navbar">
        <a href="/wedding/" data-en="Home" data-es="Inicio">Home</a>
        <a href="/wedding/timeline.php" data-en="Timeline" data-es="Cronograma">Timeline</a>
        <a href="/wedding/activities.php" data-en="Activities" data-es="Actividades">Activities</a>
        <a href="/wedding/accommodations.php" data-en="Accommodations" data-es="Alojamiento">Accommodations</a>
        <a href="/wedding/wedding-party.php" data-en="Wedding Party" data-es="Cortejo">Wedding Party</a>
        <a href="/wedding/directions.php" data-en="Directions" data-es="Direcciones">Directions</a>
        <a href="/wedding/rsvp.php" data-en="RSVP" data-es="RSVP">RSVP</a>
        <a href="/wedding/registry.php" data-en="Registry" data-es="Registro">Registry</a>
        <a href="/wedding/songs.php" data-en="Song Requests" data-es="Canciones">Song Requests</a>
        <button id="lang-toggle" onclick="toggleLang()" style="margin-left:auto; padding:5px 14px; font-weight:bold; cursor:pointer; border:2px solid #FAF7F2; background:transparent; color:#FAF7F2; border-radius:4px;">ESPAÑOL</button>
    </nav>
</header>

<main>
