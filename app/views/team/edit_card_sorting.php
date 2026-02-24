<h1 class="text-xl font-bold mb-3">Configurar Card Sorting #<?= $instance['id'] ?></h1>
<form method="post" action="/team/instance/<?= $instance['id'] ?>/status" class="mb-4">
<input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
<select name="status" class="border p-2"><option>draft</option><option <?= $instance['status']==='published'?'selected':'' ?>>published</option><option <?= $instance['status']==='closed'?'selected':'' ?>>closed</option></select>
<button class="bg-purple-600 text-white px-3 py-1">Guardar estado</button>
<a class="text-blue-700" target="_blank" href="/p/<?= e($instance['participant_token']) ?>">Link público</a>
</form>
<form method="post" action="/team/instance/<?= $instance['id'] ?>/card" class="bg-white p-4 rounded shadow space-y-2">
<input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
<select name="cs_mode" class="border p-2"><option value="open" <?= $instance['cs_mode']==='open'?'selected':'' ?>>open</option><option value="closed" <?= $instance['cs_mode']==='closed'?'selected':'' ?>>closed</option><option value="hybrid" <?= $instance['cs_mode']==='hybrid'?'selected':'' ?>>hybrid</option></select>
<label class="block"><input type="checkbox" name="allow_multi_category" value="1" <?= $instance['allow_multi_category']?'checked':'' ?>> Permitir multi-categoría</label>
<input name="max_responses" type="number" class="border p-2" placeholder="Max respuestas" value="<?= e((string)$instance['max_responses']) ?>">
<label>Tarjetas (una por línea)</label>
<textarea name="cards" class="w-full border p-2 h-32"><?php foreach($cards as $c){echo e($c['label'])."\n";} ?></textarea>
<label>Categorías semilla (una por línea)</label>
<textarea name="seed_categories" class="w-full border p-2 h-24"><?php foreach($categories as $c){echo e($c['name'])."\n";} ?></textarea>
<button class="bg-green-600 text-white px-3 py-1">Guardar configuración</button>
</form>
