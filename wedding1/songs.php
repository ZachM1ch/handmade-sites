<?php
/**
 * songs.php
 * Song request form — saves to song_requests table via MySQL.
 */

$pageTitle = "Song Requests";

$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/wedding/php/db.php';

    $song_title   = trim($_POST['song_title']   ?? '');
    $artist       = trim($_POST['artist']       ?? '');
    $requested_by = trim($_POST['requested_by'] ?? '');
    $notes        = trim($_POST['notes']        ?? '');

    if (empty($song_title)) {
        $error = 'Please enter a song title.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO song_requests (song_title, artist, requested_by, notes, submitted_at)
                VALUES (:song_title, :artist, :requested_by, :notes, NOW())
            ");
            $stmt->execute([
                ':song_title'   => $song_title,
                ':artist'       => $artist,
                ':requested_by' => $requested_by,
                ':notes'        => $notes,
            ]);
            $success = true;
        } catch (PDOException $e) {
            $error = 'Something went wrong. Please try again or let us know directly.';
        }
    }
}

include($_SERVER['DOCUMENT_ROOT'] . "/wedding/includes/header.php");
?>

<div class="wedding-section">

    <span class="section-label lang-en active">Fill the Dance Floor</span>
    <span class="section-label lang-es">Llena la Pista de Baile</span>

    <h1 class="section-title lang-en active">Song Requests</h1>
    <h1 class="section-title lang-es">Solicitud de Canciones</h1>
    <hr class="section-rule">

    <div class="song-note lang-en active">
        🎶 We want everyone on their feet! Help us build the perfect playlist by submitting your song requests below.
        Our DJ will do their best to work your favorites into the night.
    </div>
    <div class="song-note lang-es">
        🎶 ¡Queremos a todos en la pista! Ayúdanos a crear la lista de reproducción perfecta enviando tus solicitudes de canciones a continuación.
        Nuestro DJ hará todo lo posible para incluir tus favoritas en la noche.
    </div>

    <?php if ($success): ?>

        <div style="background:linear-gradient(135deg,#F8F0FF,#FFF0F8); border:1px solid var(--jewel-amethyst); border-radius:8px; padding:36px 40px; text-align:center; max-width:480px;">
            <p style="font-size:2rem; margin:0 0 10px;">🎵</p>
            <h2 style="font-family:'Cinzel',serif; font-weight:400; color:var(--accent); font-size:1.3rem; margin:0 0 12px;">
                <span class="lang-en active">Song added to the list!</span>
                <span class="lang-es">¡Canción añadida a la lista!</span>
            </h2>
            <p class="lang-en active" style="font-family:'Cormorant Garamond',serif; font-style:italic; color:var(--text-mid); margin:0 0 20px;">
                Thank you for helping make our night unforgettable.
            </p>
            <p class="lang-es" style="font-family:'Cormorant Garamond',serif; font-style:italic; color:var(--text-mid); margin:0 0 20px;">
                Gracias por ayudar a hacer nuestra noche inolvidable.
            </p>
            <a href="/wedding/songs.php" class="btn btn-outline">
                <span class="lang-en active">Add Another Song</span>
                <span class="lang-es">Agregar Otra Canción</span>
            </a>
        </div>

    <?php else: ?>

        <?php if (!empty($error)): ?>
            <div style="background:#FFF0F0; border:1px solid var(--jewel-ruby); border-radius:6px; padding:14px 18px; margin-bottom:24px; color:var(--jewel-ruby); font-size:0.9rem;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/wedding/songs.php" style="max-width:540px;">

            <!-- Song Title -->
            <div class="form-group">
                <label for="song_title">
                    <span class="lang-en active">Song Title *</span>
                    <span class="lang-es">Título de la Canción *</span>
                </label>
                <input type="text" id="song_title" name="song_title"
                       value="<?php echo htmlspecialchars($_POST['song_title'] ?? ''); ?>"
                       required placeholder="e.g. September">
            </div>

            <!-- Artist -->
            <div class="form-group">
                <label for="artist">
                    <span class="lang-en active">Artist / Band</span>
                    <span class="lang-es">Artista / Banda</span>
                </label>
                <input type="text" id="artist" name="artist"
                       value="<?php echo htmlspecialchars($_POST['artist'] ?? ''); ?>"
                       placeholder="e.g. Earth, Wind &amp; Fire">
            </div>

            <!-- Requested By -->
            <div class="form-group">
                <label for="requested_by">
                    <span class="lang-en active">Your Name</span>
                    <span class="lang-es">Tu Nombre</span>
                </label>
                <input type="text" id="requested_by" name="requested_by"
                       value="<?php echo htmlspecialchars($_POST['requested_by'] ?? ''); ?>"
                       placeholder="<?php echo 'Optional / Opcional'; ?>">
            </div>

            <!-- Notes -->
            <div class="form-group">
                <label for="notes">
                    <span class="lang-en active">Anything to Add?</span>
                    <span class="lang-es">¿Algo Más?</span>
                </label>
                <textarea id="notes" name="notes" rows="2"
                          placeholder="<?php echo 'Optional / Opcional'; ?>"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn">
                <span class="lang-en active">Submit Request</span>
                <span class="lang-es">Enviar Solicitud</span>
            </button>

        </form>

    <?php endif; ?>

</div>

<?php include($_SERVER['DOCUMENT_ROOT'] . "/wedding/includes/footer.php"); ?>
