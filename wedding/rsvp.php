<?php
$pageTitle = "RSVP";

$success = false;
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    require($_SERVER['DOCUMENT_ROOT'] . "/wedding/db.php");

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $attending = $_POST['attending'] ?? '';
    $entree = $_POST['entree'] ?? '';
    $dietary_notes = trim($_POST['dietary_notes'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && in_array($attending, ['yes', 'no']))
    {
        try
        {
            $stmt = $pdo->prepare("INSERT INTO rsvps (name, email, attending, entree, dietary_notes, message) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $attending, $entree, $dietary_notes, $message]);
            $success = true;
        }
        catch (PDOException $e)
        {
            $error = true;
        }
    }
    else
    {
        $error = true;
    }
}

include($_SERVER['DOCUMENT_ROOT'] . "/wedding/includes/header.php");
?>



<section class="card">
    <h1 data-en="RSVP" data-es="Confirmación de Asistencia">RSVP</h1>

    <?php if ($success): ?>
        <p data-en="Thank you! Your RSVP has been received." data-es="¡Gracias! Su confirmación ha sido recibida.">
            Thank you! Your RSVP has been received.
        </p>
    <?php elseif ($error): ?>
        <p data-en="Something went wrong. Please try again or contact us directly."
           data-es="Algo salió mal. Por favor inténtelo de nuevo o contáctenos directamente.">
            Something went wrong. Please try again or contact us directly.
        </p>
    <?php endif; ?>

    <?php if (!$success): ?>
        <form method="POST" action="/wedding/rsvp.php">

            <p>
                <label data-en="Your Name *" data-es="Su Nombre *">Your Name *</label><br>
                <input type="text" name="name" required style="width:100%; padding:8px; box-sizing:border-box;">
            </p>

            <p>
                <label data-en="Email Address" data-es="Correo Electrónico">Email Address</label><br>
                <input type="email" name="email" style="width:100%; padding:8px; box-sizing:border-box;">
            </p>

            <p>
                <label data-en="Will you be attending? *" data-es="¿Asistirá? *">Will you be attending? *</label><br>
                <select id="attendingDropdown" name="attending" required style="padding:8px;">
                    <option value="" data-en="-- Select --" data-es="-- Seleccionar --">-- Select --</option>
                    <option value="yes" data-en="Yes, I will attend" data-es="Sí, asistiré">Yes, I will attend</option>
                    <option value="no" data-en="No, I cannot attend" data-es="No, no podré asistir">No, I cannot
                        attend
                    </option>
                </select>
            </p>

            <div id="foodFields">
                <p>
                    <label data-en="Food:" data-es="Comida:">Food:</label><br>

                    <ul style="list-style-type: square;">
                        <li><b>(Beef)</b> Slow cooked boneless black angus short rib</li>
                        <li><b>(Chicken)</b> Chicken breast, citrus brined all natural</li>
                        <li><b>(Fish)</b> Salmon herb and citrus marinated. Orange, honey and dill sour crème sauce</li>
                        <li><b>(Vegetable)</b> Vegetable Napoleon</li>
                    </ul>

                    <select name="entree" style="padding:8px;">
                        <option value="" data-en="-- Select --" data-es="-- Seleccionar --">-- Select --</option>
                        <option value="beef" data-en="Beef" data-es="Beef">Beef</option>
                        <option value="chix" data-en="Chicken" data-es="Chicken">Chicken</option>
                        <option value="fish" data-en="Fish" data-es="Fish">Fish</option>
                        <option value="veg" data-en="Vegetable" data-es="Vegetable">Vegetable</option>
                    </select>
                </p>

                <p>
                    <label data-en="Dietary Restrictions or Allergies" data-es="Restricciones Dietéticas o Alergias">Dietary
                        Restrictions or Allergies</label><br>
                    <textarea name="dietary_notes" rows="3"
                              style="width:100%; padding:8px; box-sizing:border-box;"></textarea>
                </p>
            </div>

            <script>
                const attendingDropdown = document.getElementById('attendingDropdown');
                const foodFields = document.getElementById('foodFields');

                // Listen for selection changes
                attendingDropdown.addEventListener('change', function ()
                {
                    // If the user selected 'yes', show the fields. Otherwise, hide them.
                    if (this.value === 'yes')
                    {
                        foodFields.style.display = 'block';
                    }
                    else
                    {
                        foodFields.style.display = 'none';
                    }
                });
            </script>

            <p>
                <label data-en="Message for the Couple (optional)" data-es="Mensaje para la Pareja (opcional)">Message
                    for the Couple (optional)</label><br>
                <textarea name="message" rows="4" style="width:100%; padding:8px; box-sizing:border-box;"></textarea>
            </p>

            <p>
                <button type="submit" class="btn" data-en="Submit RSVP" data-es="Enviar Confirmación">Submit RSVP
                </button>
            </p>

        </form>
    <?php endif; ?>
</section>

<?php include($_SERVER['DOCUMENT_ROOT'] . "/wedding/includes/footer.php"); ?>
