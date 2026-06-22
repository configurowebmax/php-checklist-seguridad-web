<?php
/**
 * Checklist de Seguridad Web - Auditoría con scoring
 */
header('Content-Type: text/html; charset=utf-8');

$url = '';
$resultado = null;

// Definición del checklist de seguridad
$categorias = [
    'HTTPS y Certificado SSL' => [
        ['id'=>'https', 'texto'=>'El sitio usa HTTPS (certificado SSL válido)', 'peso'=>3, 'tip'=>'Google penaliza sitios sin HTTPS. Usa Let\'s Encrypt (gratis) o el SSL de tu hosting.'],
        ['id'=>'hsts', 'texto'=>'Header HSTS (Strict-Transport-Security) configurado', 'peso'=>2, 'tip'=>'Agrega el header: Strict-Transport-Security: max-age=31536000; includeSubDomains'],
        ['id'=>'mixed', 'texto'=>'Sin contenido mixto (HTTP dentro de HTTPS)', 'peso'=>2, 'tip'=>'Cambia todas las URLs internas de http:// a https:// o usa // (protocol-relative).'],
    ],
    'Headers de Seguridad' => [
        ['id'=>'xframe', 'texto'=>'X-Frame-Options configurado (previene clickjacking)', 'peso'=>2, 'tip'=>'Agrega: X-Frame-Options: SAMEORIGIN en tu .htaccess o config del servidor.'],
        ['id'=>'xcontent', 'texto'=>'X-Content-Type-Options: nosniff', 'peso'=>1, 'tip'=>'Previene que el navegador interprete archivos con MIME type incorrecto.'],
        ['id'=>'xss', 'texto'=>'X-XSS-Protection habilitado', 'peso'=>1, 'tip'=>'Agrega: X-XSS-Protection: 1; mode=block (legacy pero útil para IE).'],
        ['id'=>'csp', 'texto'=>'Content-Security-Policy configurado', 'peso'=>3, 'tip'=>'CSP previene inyección de scripts maliciosos. Empieza con: default-src \'self\''],
        ['id'=>'referrer', 'texto'=>'Referrer-Policy configurado', 'peso'=>1, 'tip'=>'Usa: Referrer-Policy: strict-origin-when-cross-origin'],
        ['id'=>'permissions', 'texto'=>'Permissions-Policy configurado', 'peso'=>1, 'tip'=>'Controla acceso a APIs del navegador: geolocalización, cámara, micrófono, etc.'],
    ],
    'Autenticación y Sesiones' => [
        ['id'=>'passwords', 'texto'=>'Contraseñas hasheadas (bcrypt/argon2)', 'peso'=>3, 'tip'=>'Nunca almacenes passwords en texto plano. Usa password_hash() en PHP.'],
        ['id'=>'2fa', 'texto'=>'Autenticación de dos factores (2FA) disponible', 'peso'=>2, 'tip'=>'TOTP (Google Authenticator) o SMS como segundo factor.'],
        ['id'=>'session', 'texto'=>'Cookies de sesión con HttpOnly y Secure', 'peso'=>2, 'tip'=>'session.cookie_httponly=1 y session.cookie_secure=1 en php.ini'],
        ['id'=>'csrf', 'texto'=>'Protección CSRF implementada (tokens)', 'peso'=>3, 'tip'=>'Genera un token único por formulario y valídalo en cada POST.'],
        ['id'=>'bruteforce', 'texto'=>'Protección contra fuerza bruta (rate limiting)', 'peso'=>2, 'tip'=>'Limita intentos de login: máximo 5 intentos, luego bloqueo temporal.'],
    ],
    'Validación de Datos' => [
        ['id'=>'sqli', 'texto'=>'Protección contra SQL Injection (prepared statements)', 'peso'=>3, 'tip'=>'Usa SIEMPRE prepared statements con PDO o MySQLi. Nunca concatenes variables en SQL.'],
        ['id'=>'xss_output', 'texto'=>'Escape de output (prevención XSS)', 'peso'=>3, 'tip'=>'htmlspecialchars() en PHP para todo output. Nunca imprimas datos del usuario sin escapar.'],
        ['id'=>'upload', 'texto'=>'Validación de archivos subidos (tipo, tamaño)', 'peso'=>2, 'tip'=>'Verifica MIME type real (no solo extensión), limita tamaño y guarda fuera de public_html.'],
        ['id'=>'input', 'texto'=>'Validación de inputs en servidor (no solo JS)', 'peso'=>2, 'tip'=>'La validación en JavaScript se puede saltar. Siempre valida también en el backend.'],
    ],
    'Infraestructura' => [
        ['id'=>'updates', 'texto'=>'Software actualizado (CMS, plugins, dependencias)', 'peso'=>3, 'tip'=>'El 60% de los hackeos explotan vulnerabilidades conocidas en software desactualizado.'],
        ['id'=>'backup', 'texto'=>'Backups automáticos configurados', 'peso'=>2, 'tip'=>'Backup diario de archivos y base de datos. Prueba la restauración regularmente.'],
        ['id'=>'errors', 'texto'=>'Errores de PHP/servidor ocultos en producción', 'peso'=>2, 'tip'=>'display_errors=Off en producción. Registra errores en logs, no los muestres al usuario.'],
        ['id'=>'directory', 'texto'=>'Listado de directorios deshabilitado', 'peso'=>1, 'tip'=>'Agrega: Options -Indexes en tu .htaccess'],
        ['id'=>'sensitive', 'texto'=>'Archivos sensibles protegidos (.env, .git, logs)', 'peso'=>2, 'tip'=>'Bloquea acceso en .htaccess: FilesMatch para .env, .git, .sql, .log'],
        ['id'=>'robotstxt', 'texto'=>'robots.txt no expone rutas sensibles', 'peso'=>1, 'tip'=>'No uses Disallow para ocultar carpetas sensibles — eso las hace más visibles.'],
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = trim($_POST['url'] ?? '');
    $marcados = $_POST['checks'] ?? [];

    $totalPeso = 0;
    $pesoObtenido = 0;
    $detalles = [];

    foreach ($categorias as $catNombre => $items) {
        $catDetalles = [];
        foreach ($items as $item) {
            $totalPeso += $item['peso'];
            $marcado = in_array($item['id'], $marcados);
            if ($marcado) $pesoObtenido += $item['peso'];
            $catDetalles[] = array_merge($item, ['marcado' => $marcado]);
        }
        $detalles[$catNombre] = $catDetalles;
    }

    $porcentaje = $totalPeso > 0 ? round(($pesoObtenido / $totalPeso) * 100) : 0;

    if ($porcentaje >= 80) { $nivel = 'Excelente'; $color = '#10b981'; $emoji = '🛡️'; }
    elseif ($porcentaje >= 60) { $nivel = 'Bueno'; $color = '#3b82f6'; $emoji = '✅'; }
    elseif ($porcentaje >= 40) { $nivel = 'Regular'; $color = '#f59e0b'; $emoji = '⚠️'; }
    else { $nivel = 'Crítico'; $color = '#ef4444'; $emoji = '🚨'; }

    $resultado = [
        'url' => $url,
        'porcentaje' => $porcentaje,
        'nivel' => $nivel,
        'color' => $color,
        'emoji' => $emoji,
        'pesoObtenido' => $pesoObtenido,
        'totalPeso' => $totalPeso,
        'detalles' => $detalles,
        'totalChecks' => count($marcados),
        'totalItems' => array_sum(array_map('count', $categorias)),
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checklist de Seguridad Web Online | ConfiguroWeb</title>
<meta name="description" content="Audita la seguridad de tu sitio web con este checklist interactivo. Verifica HTTPS, headers, autenticación, SQL injection y más. Gratis.">
<meta name="keywords" content="checklist seguridad web, auditoría seguridad, HTTPS, headers seguridad, SQL injection, XSS, CSRF">
<meta property="og:type" content="website">
<meta property="og:title" content="Checklist de Seguridad Web Online">
<meta property="og:description" content="Audita la seguridad de tu sitio web con un checklist interactivo y scoring.">
<link rel="canonical" href="https://demoscweb.com/github/php-checklist-seguridad-web/">
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"WebApplication","name":"Checklist Seguridad Web","applicationCategory":"UtilitiesApplication","operatingSystem":"Any","offers":{"@type":"Offer","price":"0","priceCurrency":"USD"},"author":{"@type":"Person","name":"ConfiguroWeb","url":"https://configuroweb.com"}}
</script>
<link rel="stylesheet" href="assets/style.css">
<style>
.check-item{display:flex;align-items:flex-start;gap:.6rem;padding:.6rem .8rem;border-radius:6px;margin-bottom:.3rem;transition:background .15s}
.check-item:hover{background:rgba(255,255,255,.03)}
.check-item input[type=checkbox]{width:18px;height:18px;margin-top:2px;accent-color:var(--success);flex-shrink:0;cursor:pointer}
.check-item label{cursor:pointer;font-size:.9rem;flex:1}
.check-item .peso{font-size:.7rem;background:var(--border);padding:1px 6px;border-radius:4px;color:var(--muted);white-space:nowrap}
.cat-titulo{font-size:1rem;font-weight:700;margin:1.5rem 0 .5rem;padding-bottom:.3rem;border-bottom:1px solid var(--border)}
.tip{font-size:.75rem;color:var(--muted);margin-left:1.8rem;margin-top:.1rem}
.barra-progreso{width:100%;height:12px;background:var(--surface);border-radius:6px;overflow:hidden;margin-top:.5rem}
.barra-fill{height:100%;border-radius:6px;transition:width .3s}
main{max-width:700px}
</style>
</head>
<body>
<header>
  <h1>🛡️ Checklist de Seguridad Web</h1>
  <p class="subtitle">Auditoría interactiva con scoring ponderado</p>
</header>
<main>
  <form method="POST">
    <label for="url">URL del sitio (opcional, para referencia)</label>
    <input type="text" name="url" id="url" value="<?php echo htmlspecialchars($url ?: ($_POST['url'] ?? '')); ?>" placeholder="https://midominio.com">

    <?php foreach ($categorias as $catNombre => $items): ?>
    <div class="cat-titulo"><?php echo htmlspecialchars($catNombre); ?></div>
    <?php foreach ($items as $item): ?>
    <div class="check-item">
      <input type="checkbox" name="checks[]" value="<?php echo $item['id']; ?>" id="c_<?php echo $item['id']; ?>"
        <?php if (isset($_POST['checks']) && in_array($item['id'], $_POST['checks'])) echo 'checked'; ?>>
      <div style="flex:1">
        <label for="c_<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['texto']); ?></label>
        <div class="tip">💡 <?php echo htmlspecialchars($item['tip']); ?></div>
      </div>
      <span class="peso">×<?php echo $item['peso']; ?></span>
    </div>
    <?php endforeach; ?>
    <?php endforeach; ?>

    <button type="submit" class="btn-primary">🛡️ Evaluar Seguridad</button>
  </form>

  <?php if ($resultado): ?>
  <div class="resultado" style="margin-top:1.5rem;background:linear-gradient(135deg,<?php echo $resultado['porcentaje']>=60?'#052e1a,#10b981':($resultado['porcentaje']>=40?'#422006,#f59e0b':'#450a0a,#ef4444'); ?>)">
    <span class="etiqueta">Nivel de Seguridad</span>
    <div class="valor"><?php echo $resultado['emoji']; ?> <?php echo $resultado['porcentaje']; ?>%</div>
    <p style="margin-top:.3rem;opacity:.9;font-size:1.1rem;font-weight:600"><?php echo $resultado['nivel']; ?></p>
    <p style="opacity:.7;font-size:.85rem"><?php echo $resultado['totalChecks']; ?>/<?php echo $resultado['totalItems']; ?> puntos verificados · Peso: <?php echo $resultado['pesoObtenido']; ?>/<?php echo $resultado['totalPeso']; ?></p>
    <div class="barra-progreso">
      <div class="barra-fill" style="width:<?php echo $resultado['porcentaje']; ?>%;background:<?php echo $resultado['color']; ?>"></div>
    </div>
  </div>

  <!-- Items no marcados (mejoras pendientes) -->
  <?php
  $pendientes = [];
  foreach ($resultado['detalles'] as $cat => $items) {
      foreach ($items as $item) {
          if (!$item['marcado']) $pendientes[] = $item;
      }
  }
  if (!empty($pendientes)):
  ?>
  <div style="background:var(--surface);padding:1rem;border-radius:var(--radius);margin-top:1rem;border-left:4px solid #f59e0b">
    <h3 style="font-size:.95rem;margin-bottom:.5rem;color:#f59e0b">⚠️ Mejoras pendientes (<?php echo count($pendientes); ?>)</h3>
    <?php foreach ($pendientes as $p): ?>
    <div style="padding:.3rem 0;font-size:.85rem;color:var(--muted)">
      ❌ <?php echo htmlspecialchars($p['texto']); ?> <span class="peso">×<?php echo $p['peso']; ?></span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>

  <section class="info">
    <h2>¿Cómo funciona el scoring?</h2>
    <p>Cada punto del checklist tiene un <strong>peso</strong> (×1 a ×3) según su impacto en la seguridad. Los puntos críticos como <strong>SQL Injection</strong>, <strong>HTTPS</strong> y <strong>CSRF</strong> tienen peso ×3.</p>
    <p><strong>80-100%:</strong> 🛡️ Excelente — Tu sitio sigue las mejores prácticas.</p>
    <p><strong>60-79%:</strong> ✅ Bueno — Hay margen de mejora en algunos puntos.</p>
    <p><strong>40-59%:</strong> ⚠️ Regular — Vulnerabilidades importantes sin cubrir.</p>
    <p><strong>0-39%:</strong> 🚨 Crítico — Tu sitio necesita atención urgente.</p>
  </section>
</main>
<footer>
  <p>Desarrollado por <a href="https://configuroweb.com" target="_blank">ConfiguroWeb</a> ·
     <a href="https://appscweb.com/citas/" target="_blank">Sistema de Citas</a> ·
     <a href="https://appscweb.com/negocios/" target="_blank">Gestión de Negocios</a></p>
  <p>&copy; <?php echo date('Y'); ?> ConfiguroWeb</p>
</footer>
<script src="assets/script.js"></script>
</body>
</html>
