<?php $pageTitle = "Directions"; include($_SERVER['DOCUMENT_ROOT']."/wedding/includes/header.php"); ?>

<div class="wedding-section">

    <span class="section-label lang-en active">Getting There</span>
    <span class="section-label lang-es">Cómo Llegar</span>

    <h1 class="section-title lang-en active">Venue &amp; Directions</h1>
    <h1 class="section-title lang-es">Sede y Cómo Llegar</h1>
    <hr class="section-rule">

    <!-- Venue Card -->
    <div class="card" style="text-align:center; margin-bottom:36px;">
        <p style="font-size:2rem; margin:0 0 10px;">🏛️</p>
        <h2 style="margin-bottom:4px;">The Grand Pavilion</h2>
        <p style="color:var(--text-mid); font-size:0.95rem; margin:0 0 16px;">
            1000 Pavilion Drive, Bankton, NJ 07000
        </p>
        <a href="https://maps.google.com/?q=1000+Pavilion+Drive+Bankton+NJ" target="_blank" rel="noopener" class="btn btn-ruby">
            <span class="lang-en active">Open in Google Maps</span>
            <span class="lang-es">Abrir en Google Maps</span>
        </a>
    </div>

    <!-- From the North -->
    <div class="directions-block">
        <h3>
            <span class="lang-en active">🧭 From the North (New York / Northern NJ)</span>
            <span class="lang-es">🧭 Desde el Norte (Nueva York / Norte de NJ)</span>
        </h3>
        <p class="lang-en active">
            Take the Garden State Parkway South to Exit 88. Merge onto County Road 9 South. Continue for approximately 4 miles and turn left onto Pavilion Drive. The Grand Pavilion will be on your right.
        </p>
        <p class="lang-es">
            Toma el Garden State Parkway hacia el Sur hasta la Salida 88. Fusiona con County Road 9 Sur. Continúa aproximadamente 6.5 km y gira a la izquierda en Pavilion Drive. El Gran Pabellón estará a tu derecha.
        </p>
    </div>

    <!-- From the South -->
    <div class="directions-block">
        <h3>
            <span class="lang-en active">🧭 From the South (Philadelphia / Southern NJ)</span>
            <span class="lang-es">🧭 Desde el Sur (Filadelfia / Sur de NJ)</span>
        </h3>
        <p class="lang-en active">
            Take the New Jersey Turnpike North to Exit 7A, then follow I-195 East to Route 9 North. Continue on Route 9 North for approximately 6 miles, then turn right onto Pavilion Drive.
        </p>
        <p class="lang-es">
            Toma el New Jersey Turnpike hacia el Norte hasta la Salida 7A, luego sigue la I-195 Este hacia la Ruta 9 Norte. Continúa por la Ruta 9 Norte aproximadamente 10 km, luego gira a la derecha en Pavilion Drive.
        </p>
    </div>

    <!-- From the West -->
    <div class="directions-block">
        <h3>
            <span class="lang-en active">🧭 From the West (Pennsylvania)</span>
            <span class="lang-es">🧭 Desde el Oeste (Pensilvania)</span>
        </h3>
        <p class="lang-en active">
            Cross into New Jersey via I-78 East or the Delaware Memorial Bridge. Follow I-78 East to Exit 44, then take Route 22 East to County Road 9 South. Turn right onto Pavilion Drive.
        </p>
        <p class="lang-es">
            Cruza a Nueva Jersey por la I-78 Este o el Delaware Memorial Bridge. Sigue la I-78 Este hasta la Salida 44, luego toma la Ruta 22 Este hasta County Road 9 Sur. Gira a la derecha en Pavilion Drive.
        </p>
    </div>

    <!-- Parking -->
    <div class="card" style="margin-top:36px;">
        <h2>
            <span class="lang-en active">🅿️ Parking</span>
            <span class="lang-es">🅿️ Estacionamiento</span>
        </h2>
        <p class="lang-en active">
            Complimentary parking is available on-site at The Grand Pavilion. The main lot is located directly behind the venue off Pavilion Drive. Overflow parking is available in the adjacent field for larger events.
        </p>
        <p class="lang-es">
            Hay estacionamiento gratuito disponible en el lugar en El Gran Pabellón. El estacionamiento principal está ubicado directamente detrás de la sede en Pavilion Drive. Hay estacionamiento adicional disponible en el campo adyacente para eventos grandes.
        </p>
        <p class="lang-en active" style="margin-top:14px; font-size:0.9rem; color:var(--text-mid);">
            <strong>Shuttle Service:</strong> Complimentary shuttles will run between The Pavilion Inn and the venue beginning at 3:00 PM and running until 1:00 AM. See the <a href="/wedding/accommodations.php" style="color:var(--accent);">Accommodations</a> page for details.
        </p>
        <p class="lang-es" style="margin-top:14px; font-size:0.9rem; color:var(--text-mid);">
            <strong>Servicio de Transporte:</strong> Habrá transporte gratuito entre The Pavilion Inn y la sede a partir de las 3:00 PM hasta la 1:00 AM. Consulta la página de <a href="/wedding/accommodations.php" style="color:var(--accent);">Hospedaje</a> para más detalles.
        </p>
    </div>

    <!-- Map placeholder -->
    <div class="map-placeholder">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d787.5791615033115!2d-74.99781090185219!3d40.82538106627873!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c478ee21f20227%3A0xb533b20d4d2a1275!2sJack&#39;s%20Barn!5e1!3m2!1sen!2sus!4v1773701075222!5m2!1sen!2sus" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        <p class="lang-es" style="margin:0; font-size:0.9rem; color:var(--text-mid);">
            Inserta aquí tu mapa de Google Maps.<br>
            Reemplaza este bloque con: <code style="font-size:0.8rem;">&lt;iframe src="TU_URL_DE_INCRUSTACIÓN" ...&gt;&lt;/iframe&gt;</code>
        </p>
    </div>

</div>

<?php include($_SERVER['DOCUMENT_ROOT']."/wedding/includes/footer.php"); ?>
