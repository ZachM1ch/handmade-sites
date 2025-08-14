<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? "My Website"; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header>
    <nav class="navbar">
        <a href="/index.php">Home</a>
        <a href="/portfolio/">Portfolio</a>
        <a href="/gallery/">Gallery</a>
        <a href="/stories/">Stories</a>
    </nav>
	<!-- Theme toggle button -->
	<button id="themeToggleBtn" class="btn">Switch Theme</button>

	<!-- Load script -->
	<script src="/assets/js/theme-toggle.js"></script>
</header>
<main>
