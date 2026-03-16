<?php
/**
 * rsvp.php
 * Handles RSVP form display and MySQL submission.
 * Requires: /wedding/php/db.php for database connection.
 */

$pageTitle = "RSVP";

$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/wedding/php/db.php';

    // Sanitize inputs
    $first_name   = trim($_POST['first_name']   ?? '');
    $last_name    = trim($_POST['last_name']    ?? '');
    $email        = trim($_POST['email']        ?? '');
    $attending    = trim($_POST['attending']    ?? '');
    $guest_count  = (int)($_POST['guest_count'] ?? 1);
    $meal_choice  = trim($_POST['meal_choice']  ?? '');
    $dietary      = trim($_POST['dietary']      ?? '');
    $notes        = trim($_POST['notes']        ?? '');

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($attending)) {
        $error = 'Please fill in all required fields.';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO rsvps
                    (first_name, last_name, email, attending, guest_count, meal_choice, dietary_restrictions, notes, submitted_at)
                VALUES
                    (:first_name, :last_name, :email, :attending, :guest_count, :meal_choice, :dietary, :notes, NOW())
            ");
            $stmt->execute([
                ':first_name'  => $first_name,
                ':last_name'   => $last_name,
                ':email'       => $email,
                ':attending'   => $attending,
                ':guest_count' => $guest_count,
                ':meal_choice' => $meal_choice,
                ':dietary'     => $dietary,
                ':notes'       => $notes,
            ]);
            $success = true;
        } catch (PDOException $e) {
            $error = 'Something went wrong saving your RSVP. Please try again or contact us directly.';
            // Log error server-side: error_log($e->getMessage());
        }
    }
}

include($_SERVER['DOCUMENT_ROOT'] . "/wedding/includes/header.php");
?>

<div class="wedding-section">

    <span class="section-label lang-en active">Kindly Reply By May 1, 2026</span>
    <span class="section-label lang-es">Por Favor Responde Antes del 1 de Mayo, 2026</span>

    <h1 class="section-title">RSVP</h1>
    <hr class="section-rule">

    <?php if ($success): ?>
        <!-- Success Message -->
        <div style="background:linear-gradient(135deg,#F0FAF5,#F8F0FF); border:1px solid var(--jewel-emerald); border-radius:8px; padding:36px 40px; text-align:center; max-width:560px;">
            <p style="font-size:2rem; margin:0 0 12px;">💌</p>
            <h2 style="font-family:'Cinzel',serif; font-weight:400; color:var(--jewel-emerald); margin:0 0 12px; font-size:1.4rem;">
                <span class="lang-en active">We received your RSVP!</span>
                <span class="lang-es">¡Recibimos tu RSVP!</span>
            </h2>
            <p class="lang-en active" style="font-family:'Cormorant Garamond',serif; font-style:italic; font-size:1.15rem; color:var(--text-mid); margin:0;">
                Thank you, <?php echo htmlspecialchars($first_name); ?>. We can't wait to celebrate with you.
            </p>
            <p class="lang-es" style="font-family:'Cormorant Garamond',serif; font-style:italic; font-size:1.15rem; color:var(--text-mid); margin:0;">
                Gracias, <?php echo htmlspecialchars($first_name); ?>. No podemos esperar para celebrar contigo.
            </p>
        </div>

    <?php else: ?>

        <?php if (!empty($error)): ?>
            <div style="background:#FFF0F0; border:1px solid var(--jewel-ruby); border-radius:6px; padding:14px 18px; margin-bottom:24px; color:var(--jewel-ruby); font-size:0.9rem;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="lang-en active" style="margin-bottom:28px; font-family:'Cormorant Garamond',serif; font-style:italic; font-size:1.1rem; color:var(--text-mid);">
            Please fill out one form per family or household. If you have any questions, reach out to us at
            <a href="mailto:wedding@example.com" style="color:var(--accent);">wedding@example.com</a>.
        </div>
        <div class="lang-es" style="margin-bottom:28px; font-family:'Cormorant Garamond',serif; font-style:italic; font-size:1.1rem; color:var(--text-mid);">
            Por favor completa un formulario por familia u hogar. Si tienes alguna pregunta, contáctanos en
            <a href="mailto:wedding@example.com" style="color:var(--accent);">wedding@example.com</a>.
        </div>

        <form method="POST" action="/wedding/rsvp.php" class="rsvp-form">

            <!-- Name Row -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label for="first_name">
                        <span class="lang-en active">First Name *</span>
                        <span class="lang-es">Nombre *</span>
                    </label>
                    <input type="text" id="first_name" name="first_name"
                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                           required autocomplete="given-name">
                </div>
                <div class="form-group">
                    <label for="last_name">
                        <span class="lang-en active">Last Name *</span>
                        <span class="lang-es">Apellido *</span>
                    </label>
                    <input type="text" id="last_name" name="last_name"
                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                           required autocomplete="family-name">
                </div>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">
                    <span class="lang-en active">Email Address</span>
                    <span class="lang-es">Correo Electrónico</span>
                </label>
                <input type="email" id="email" name="email"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       autocomplete="email" placeholder="optional">
            </div>

            <!-- Attending -->
            <div class="form-group">
                <label>
                    <span class="lang-en active">Will you be attending? *</span>
                    <span class="lang-es">¿Asistirás? *</span>
                </label>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="attending" value="yes"
                               <?php echo (($_POST['attending'] ?? '') === 'yes') ? 'checked' : ''; ?> required>
                        <span class="lang-en active">Joyfully accepts</span>
                        <span class="lang-es">Acepta con alegría</span>
                    </label>
                    <label>
                        <input type="radio" name="attending" value="no"
                               <?php echo (($_POST['attending'] ?? '') === 'no') ? 'checked' : ''; ?>>
                        <span class="lang-en active">Regretfully declines</span>
                        <span class="lang-es">Declina con pesar</span>
                    </label>
                </div>
            </div>

            <!-- Guest Count -->
            <div class="form-group">
                <label for="guest_count">
                    <span class="lang-en active">Number of Guests (including yourself)</span>
                    <span class="lang-es">Número de Invitados (incluyéndote)</span>
                </label>
                <select id="guest_count" name="guest_count">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo (($_POST['guest_count'] ?? 1) == $i) ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Meal Choice -->
            <div class="form-group">
                <label for="meal_choice">
                    <span class="lang-en active">Meal Preference</span>
                    <span class="lang-es">Preferencia de Comida</span>
                </label>
                <select id="meal_choice" name="meal_choice">
                    <option value="">
                        --
                        <span class="lang-en active"> Select</span>
                        <span class="lang-es"> Seleccionar</span>
                    </option>
                    <option value="chicken" <?php echo (($_POST['meal_choice'] ?? '') === 'chicken') ? 'selected' : ''; ?>>
                        🍗
                        <span class="lang-en active"> Chicken</span>
                        <span class="lang-es"> Pollo</span>
                    </option>
                    <option value="beef" <?php echo (($_POST['meal_choice'] ?? '') === 'beef') ? 'selected' : ''; ?>>
                        🥩
                        <span class="lang-en active"> Beef</span>
                        <span class="lang-es"> Res</span>
                    </option>
                    <option value="fish" <?php echo (($_POST['meal_choice'] ?? '') === 'fish') ? 'selected' : ''; ?>>
                        🐟
                        <span class="lang-en active"> Fish</span>
                        <span class="lang-es"> Pescado</span>
                    </option>
                    <option value="vegetarian" <?php echo (($_POST['meal_choice'] ?? '') === 'vegetarian') ? 'selected' : ''; ?>>
                        🥗
                        <span class="lang-en active"> Vegetarian</span>
                        <span class="lang-es"> Vegetariano</span>
                    </option>
                </select>
            </div>

            <!-- Dietary Restrictions -->
            <div class="form-group">
                <label for="dietary">
                    <span class="lang-en active">Dietary Restrictions or Allergies</span>
                    <span class="lang-es">Restricciones Dietéticas o Alergias</span>
                </label>
                <input type="text" id="dietary" name="dietary"
                       value="<?php echo htmlspecialchars($_POST['dietary'] ?? ''); ?>"
                       placeholder="<?php echo 'None / Ninguna'; ?>">
            </div>

            <!-- Notes -->
            <div class="form-group">
                <label for="notes">
                    <span class="lang-en active">Anything Else We Should Know?</span>
                    <span class="lang-es">¿Algo Más Que Debamos Saber?</span>
                </label>
                <textarea id="notes" name="notes" rows="3"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn" style="margin-top:8px;">
                <span class="lang-en active">Send My RSVP</span>
                <span class="lang-es">Enviar Mi RSVP</span>
            </button>

        </form>

    <?php endif; ?>

</div>

<?php include($_SERVER['DOCUMENT_ROOT'] . "/wedding/includes/footer.php"); ?>
