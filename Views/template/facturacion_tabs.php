<?php
// Pestañas del modulo de Facturacion. Definir $facturacionTab = 'legajos' o 'expedientes' antes de incluir este archivo.
$facturacionTab = $facturacionTab ?? 'expedientes';
$__facturacionTabs = [
    'legajos' => ['Legajos', 'legajos/facturacion'],
    'expedientes' => ['Expedientes', 'expedientes/facturacion'],
];
?>
<div class="flex gap-2 mb-6 border-b border-gray-200">
    <?php foreach ($__facturacionTabs as $__clave => $__tab): ?>
    <a href="<?php echo base_url() . $__tab[1]; ?>"
        class="px-5 py-3 text-sm font-bold uppercase tracking-wide <?php echo $__clave === $facturacionTab ? 'border-b-4 border-scantec-blue text-scantec-blue' : 'text-scantec-gray hover:text-scantec-blue'; ?>">
        <?php echo $__tab[0]; ?>
    </a>
    <?php endforeach; ?>
</div>