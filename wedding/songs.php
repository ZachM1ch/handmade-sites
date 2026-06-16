<?php
$pageTitle = "Song Requests";

$success = false;
$error   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require($_SERVER['DOCUMENT_ROOT']."/wedding/db.php");

    $name       = trim($_POST['name'] ?? '');
    $song_title = trim($_POST['song_title'] ?? '');
    $artist     = trim($_POST['artist'] ?? '');

    if ($song_title) {
        try {
            $stmt = $pdo->prepare("INSERT INTO song_requests (name, song_title, artist) VALUES (?, ?, ?)");
            $stmt->execute([$name, $song_title, $artist]);
            $success = true;
        } catch (PDOException $e) {
            $error = true;
        }
    } else {
        $error = true;
    }
}

include($_SERVER['DOCUMENT_ROOT']."/wedding/includes/header.php");
?>

<section class="card">
    <h1 data-en="Song Requests" data-es="Petición de Canciones">Song Requests</h1>
    <p data-en="Help us build the perfect playlist! Submit a song you'd love to hear at the reception."
       data-es="¡Ayúdenos a crear la lista de reproducción perfecta! Envíe una canción que le gustaría escuchar en la recepción.">
        Help us build the perfect playlist! Submit a song you'd love to hear at the reception.
    </p>

    <?php if ($success): ?>
        <p data-en="Thank you for your request!" data-es="¡Gracias por su petición!">
            Thank you for your request!
        </p>
    <?php elseif ($error): ?>
        <p data-en="Something went wrong. Please try again."
           data-es="Algo salió mal. Por favor inténtelo de nuevo.">
            Something went wrong. Please try again.
        </p>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="POST" action="/wedding/songs.php">

        <p>
            <label data-en="Your Name (optional)" data-es="Su Nombre (opcional)">Your Name (optional)</label><br>
            <input type="text" name="name" style="width:100%; padding:8px; box-sizing:border-box;">
        </p>

        <p>
            <label data-en="Song Title *" data-es="Título de la Canción *">Song Title *</label><br>
            <input type="text" name="song_title" required style="width:100%; padding:8px; box-sizing:border-box;">
        </p>

        <p>
            <label data-en="Artist" data-es="Artista">Artist</label><br>
            <input type="text" name="artist" style="width:100%; padding:8px; box-sizing:border-box;">
        </p>

        <p>
            <button type="submit" class="btn" data-en="Submit Request" data-es="Enviar Petición">Submit Request</button>
        </p>

    </form>
    <?php endif; ?>
</section>

<?php include($_SERVER['DOCUMENT_ROOT']."/wedding/includes/footer.php"); ?>
