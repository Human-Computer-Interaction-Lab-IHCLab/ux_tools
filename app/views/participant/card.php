<div class="bg-white p-4 rounded shadow">
<h1 class="text-xl font-bold mb-2">Card Sorting</h1>
<p class="text-sm mb-3">Arrastra cada tarjeta a una categoría. No podrás enviar si falta alguna.</p>
<div id="card-sorting" data-mode="<?= e($instance['cs_mode']) ?>">
<div class="mb-2"><button id="add-category" type="button" class="bg-gray-200 px-2 py-1">+ Categoría</button></div>
<div class="grid md:grid-cols-3 gap-3" id="categories">
<?php foreach($seed as $cat): ?><div class="border rounded p-2 min-h-32 dropzone" data-category="<?= e($cat['name']) ?>"><h3 class="font-semibold"><?= e($cat['name']) ?></h3></div><?php endforeach; ?>
</div>
<h2 class="font-semibold mt-3">Tarjetas</h2>
<div id="cards" class="flex flex-wrap gap-2 mt-2"><?php foreach($cards as $c): ?><div draggable="true" data-card-id="<?= $c['id'] ?>" class="card border rounded px-2 py-1 bg-blue-100"><?= e($c['label']) ?></div><?php endforeach; ?></div>
</div>
<form method="post" action="/p/<?= e($token) ?>/card" class="mt-4">
<input type="hidden" id="payload" name="payload">
<button class="bg-green-600 text-white px-3 py-2">Enviar</button>
</form>
</div>
