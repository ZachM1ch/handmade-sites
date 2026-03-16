<?php $pageTitle = "Registry"; include($_SERVER['DOCUMENT_ROOT']."/wedding/includes/header.php"); ?>

<div class="wedding-section" style="text-align:center;">

    <span class="section-label lang-en active" style="display:block;">Give the Gift of Joy</span>
    <span class="section-label lang-es" style="display:block;">Da el Regalo de la Alegría</span>

    <h1 class="section-title lang-en active" style="margin:0 auto;">Gift Registry</h1>
    <h1 class="section-title lang-es" style="margin:0 auto;">Lista de Bodas</h1>
    <hr class="section-rule" style="margin:16px auto 36px;">

    <div class="lang-en active" style="max-width:580px; margin:0 auto 48px;">
        <p style="font-family:'Cormorant Garamond',serif; font-style:italic; font-size:1.2rem; color:var(--text-mid); line-height:1.9;">
            Your presence at our wedding is the greatest gift we could ask for. However, if you would like to give a gift, we have registered at the stores below.
        </p>
    </div>
    <div class="lang-es" style="max-width:580px; margin:0 auto 48px;">
        <p style="font-family:'Cormorant Garamond',serif; font-style:italic; font-size:1.2rem; color:var(--text-mid); line-height:1.9;">
            Tu presencia en nuestra boda es el mayor regalo que podríamos pedir. Sin embargo, si deseas dar un regalo, nos hemos registrado en las tiendas a continuación.
        </p>
    </div>

    <!-- Registry Cards -->
    <div class="card-grid" style="justify-content:center; max-width:700px; margin:0 auto 48px;">

        <div class="activity-card" style="text-align:center;">
            <span class="activity-card-icon">🏠</span>
            <h3 class="lang-en active">Crate &amp; Barrel</h3>
            <h3 class="lang-es">Crate &amp; Barrel</h3>
            <p class="lang-en active">Home goods, kitchen essentials, and décor for our new home together.</p>
            <p class="lang-es">Artículos del hogar, utensilios de cocina y decoración para nuestro nuevo hogar juntos.</p>
            <a href="https://www.crateandbarrel.com/gift-registry" target="_blank" rel="noopener" class="btn" style="margin-top:12px; font-size:0.75rem; padding:9px 18px;">
                <span class="lang-en active">View Registry</span>
                <span class="lang-es">Ver Lista</span>
            </a>
        </div>

        <div class="activity-card" style="text-align:center;">
            <span class="activity-card-icon">✈️</span>
            <h3 class="lang-en active">Honeymoon Fund</h3>
            <h3 class="lang-es">Fondo de Luna de Miel</h3>
            <p class="lang-en active">Help us experience our dream honeymoon! Contributions of any size are deeply appreciated.</p>
            <p class="lang-es">¡Ayúdanos a vivir la luna de miel de nuestros sueños! Las contribuciones de cualquier tamaño son muy apreciadas.</p>
            <a href="https://www.zola.com" target="_blank" rel="noopener" class="btn btn-ruby" style="margin-top:12px; font-size:0.75rem; padding:9px 18px;">
                <span class="lang-en active">Contribute</span>
                <span class="lang-es">Contribuir</span>
            </a>
        </div>

    </div>

    <!-- Note -->
    <div style="background:var(--parchment); border:1px solid var(--border-color); border-radius:6px; padding:20px 28px; max-width:580px; margin:0 auto;">
        <p style="margin:0; font-size:0.9rem; color:var(--text-mid);">
            <span class="lang-en active">
                Gifts can also be brought to the wedding. We will have a designated gift table at the reception.
                If you have any questions, feel free to reach out to <a href="mailto:wedding@example.com" style="color:var(--accent);">wedding@example.com</a>.
            </span>
            <span class="lang-es">
                Los regalos también se pueden traer a la boda. Tendremos una mesa designada para regalos en la recepción.
                Si tienes alguna pregunta, no dudes en contactarnos en <a href="mailto:wedding@example.com" style="color:var(--accent);">wedding@example.com</a>.
            </span>
        </p>
    </div>

</div>

<?php include($_SERVER['DOCUMENT_ROOT']."/wedding/includes/footer.php"); ?>
