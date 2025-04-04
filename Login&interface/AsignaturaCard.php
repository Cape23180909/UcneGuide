<?php
/**
 * Componente AsignaturaCard
 * Muestra una materia con interactividad avanzada
 * 
 * @param array $asignatura - Datos de la asignatura
 * @param bool $showTrends - Mostrar datos de tendencia
 */
function AsignaturaCard($asignatura, $showTrends = false) {
    $emoji = match(substr($asignatura['codigoAsignatura'], 0, 1)) {
        'I' => '👨‍💻', // Ingeniería
        'M' => '🧮',  // Matemáticas
        'P' => '🔬',  // Prácticas
        default => '📚'
    };
    
    $dificultad = rand(1, 5);
    $popularidad = rand(50, 100);
?>
<div class="asignatura-card" 
     data-codigo="<?= htmlspecialchars($asignatura['codigoAsignatura']) ?>"
     onclick="window.location='DescripcionAsignaturas.php?codigo=<?= $asignatura['codigoAsignatura'] ?>'">
    
    <!-- Encabezado con emoji dinámico -->
    <div class="card-header">
        <span class="card-emoji"><?= $emoji ?></span>
        <h3><?= htmlspecialchars($asignatura['nombreAsignatura']) ?></h3>
        <button class="btn-favorite" onclick="event.stopPropagation(); toggleFavorite(this)">
            <i class="far fa-star"></i>
        </button>
    </div>
    
    <!-- Cuerpo con información clave -->
    <div class="card-body">
        <p><?= htmlspecialchars($asignatura['descripcionCorta'] ?? 'Descripción no disponible') ?></p>
        
        <?php if ($showTrends): ?>
        <div class="trend-meter">
            <div class="trend-bar" style="--popularidad: <?= $popularidad ?>%">
                <span>Popularidad: <?= $popularidad ?>%</span>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Pie de tarjeta interactivo -->
    <div class="card-footer">
        <span class="badge"><?= htmlspecialchars($asignatura['codigoAsignatura']) ?></span>
        
        <div class="difficulty">
            <?= str_repeat('⭐', $dificultad) ?>
            <?= str_repeat('☆', 5 - $dificultad) ?>
        </div>
        
        <button class="btn-quick-action" onclick="event.stopPropagation(); showQuickActions(this)">
            <i class="fas fa-ellipsis-h"></i>
        </button>
    </div>
    
    <!-- Menú contextual flotante -->
    <div class="quick-actions-menu">
        <button onclick="addToSchedule('<?= $asignatura['codigoAsignatura'] ?>')">
            <i class="far fa-calendar-plus"></i> Agendar
        </button>
        <button onclick="shareSubject('<?= $asignatura['nombreAsignatura'] ?>')">
            <i class="fas fa-share-alt"></i> Compartir
        </button>
    </div>
</div>

<script>
// Funcionalidad del componente
function toggleFavorite(btn) {
    btn.querySelector('i').classList.toggle('far');
    btn.querySelector('i').classList.toggle('fas');
    // Aquí iría tu llamada a la API para guardar favoritos
}

function showQuickActions(btn) {
    const card = btn.closest('.asignatura-card');
    card.querySelector('.quick-actions-menu').classList.toggle('show');
}
</script>
<?php
}
?>