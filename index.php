<?php
header('Content-Type: text/html; charset=utf-8');
$input = $_POST['input'] ?? '';
$resultado = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $input !== '') {
    $resultado = $input;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checklist Seguridad Web Online | ConfiguroWeb</title>
<meta name="description" content="Checklist Seguridad Web online gratis. Herramienta web de ConfiguroWeb.">
<meta name="keywords" content="Checklist Seguridad Web, herramienta online, gratis, configuroweb">
<meta property="og:type" content="website">
<meta property="og:title" content="Checklist Seguridad Web Online">
<meta property="og:description" content="Checklist Seguridad Web online gratis.">
<link rel="canonical" href="https://demoscweb.com/github/php-checklist-seguridad-web/">
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"WebApplication","name":"Checklist Seguridad Web","applicationCategory":"UtilitiesApplication","operatingSystem":"Any","offers":{"@type":"Offer","price":"0","priceCurrency":"USD"},"author":{"@type":"Person","name":"ConfiguroWeb","url":"https://configuroweb.com"}}
</script>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
  <h1>🛡️ Checklist Seguridad Web</h1>
  <p class="subtitle">Herramienta online gratis</p>
</header>
<main>
  <form method="POST">
    <label for="input">Datos de entrada:</label>
    <textarea name="input" id="input" rows="6" placeholder="Ingresa aqui tus datos..."></textarea>
    <button type="submit" class="btn-primary">Procesar</button>
  </form>

  <?php if ($resultado !== ''): ?>
  <div class="resultado">
    <span class="etiqueta">Resultado</span>
    <div class="valor" style="font-size:1rem;padding:1rem;text-align:left;white-space:pre-wrap"><?php echo htmlspecialchars($resultado); ?></div>
  </div>
  <?php endif; ?>

  <section class="info">
    <h2>Acerca de Checklist Seguridad Web</h2>
    <p>Esta herramienta de <strong>Checklist Seguridad Web</strong> esta en desarrollo activo. Pronto con mas funciones avanzadas.</p>
  </section>
</main>
<footer>
  <p>Desarrollado por <a href="https://configuroweb.com" target="_blank">ConfiguroWeb</a> ·
     <a href="https://appscweb.com/citas/" target="_blank">Sistema de Citas</a> ·
     <a href="https://appscweb.com/negocios/" target="_blank">Gestion de Negocios</a></p>
  <p>&copy; 2026 ConfiguroWeb</p>
</footer>
<script src="assets/script.js"></script>
</body>
</html>
