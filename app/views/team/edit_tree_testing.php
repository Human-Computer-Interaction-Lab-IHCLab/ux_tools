<h1 class="text-xl font-bold mb-3">Configurar Tree Testing #<?= $instance['id'] ?></h1>
<form method="post" action="/team/instance/<?= $instance['id'] ?>/status" class="mb-4">
<input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
<select name="status" class="border p-2"><option>draft</option><option <?= $instance['status']==='published'?'selected':'' ?>>published</option><option <?= $instance['status']==='closed'?'selected':'' ?>>closed</option></select>
<button class="bg-purple-600 text-white px-3 py-1">Guardar estado</button>
<a class="text-blue-700" target="_blank" href="/p/<?= e($instance['participant_token']) ?>">Link público</a>
</form>
<form method="post" action="/team/instance/<?= $instance['id'] ?>/tree" class="bg-white p-4 rounded shadow space-y-2">
<input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
<p class="text-sm">Define nodos y tareas en JSON para MVP rápido.</p>
<textarea name="nodes_json" class="w-full border p-2 h-32" placeholder='[{"tmp_id":"1","label":"Inicio","position":1},{"tmp_id":"2","parent_tmp_id":"1","label":"Ayuda","position":1}]'><?= e(json_encode($nodes, JSON_UNESCAPED_UNICODE)) ?></textarea>
<textarea name="tasks_json" class="w-full border p-2 h-24" placeholder='[{"prompt":"¿Dónde ...?","correct_tmp_id":"2"}]'><?= e(json_encode($tasks, JSON_UNESCAPED_UNICODE)) ?></textarea>
<button class="bg-green-600 text-white px-3 py-1">Guardar árbol/tareas</button>
</form>
