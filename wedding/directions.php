<?php $pageTitle = "Directions"; include($_SERVER['DOCUMENT_ROOT']."/wedding/includes/header.php"); ?>

<section class="card">
    <h1 data-en="Directions" data-es="Direcciones">Directions</h1>

    <h2 data-en="The Grand Pavilion" data-es="The Grand Pavilion">The Grand Pavilion</h2>
    <p data-en="123 Placeholder Road, Bankton, NJ 00000"
       data-es="123 Calle Ejemplo, Bankton, Nueva Jersey 00000">
        123 Placeholder Road, Bankton, NJ 00000
    </p>

	<!-- Venue Card -->
    <div class="card" style="text-align:center; margin-bottom:36px;">
        <p style="font-size:2rem; margin:0 0 10px;">🏛️</p>
        <h2 style="margin-bottom:4px;">The Grand Pavilion</h2>
        <p style="color:var(--text-mid); font-size:0.95rem; margin:0 0 16px;">
            1487 NJ-31, Oxford, NJ 07863
        </p>
        <a href="https://maps.app.goo.gl/YEZ1PQK677XNrzzJ9" target="_blank" rel="noopener" class="btn btn-ruby">
            <span data-en="Open in Google Maps" data-es="Abrir en Google Maps">Open in Google Maps</span>
        </a>
    </div>

    <p>
        <a class="btn" href="https://maps.google.com/?q=1487+NJ-31+Oxford+NJ" target="_blank"
           data-en="Open in Google Maps" data-es="Abrir en Google Maps">Open in Google Maps</a>
    </p>

    <h2 data-en="Parking" data-es="Estacionamiento">Parking</h2>
    <p data-en="Placeholder: Add parking instructions here."
       data-es="Marcador de posición: Agregue instrucciones de estacionamiento aquí.">
        Placeholder: Add parking instructions here.
    </p>

    <h2 data-en="From the North" data-es="Desde el Norte">From the North</h2>
    <p data-en="Placeholder: Add driving directions from the north here."
       data-es="Marcador de posición: Agregue indicaciones desde el norte aquí.">
        Placeholder: Add driving directions from the north here.
    </p>

    <h2 data-en="From the South" data-es="Desde el Sur">From the South</h2>
    <p data-en="Placeholder: Add driving directions from the south here."
       data-es="Marcador de posición: Agregue indicaciones desde el sur aquí.">
        Placeholder: Add driving directions from the south here.
    </p>
</section>

<?php include($_SERVER['DOCUMENT_ROOT']."/wedding/includes/footer.php"); ?>
