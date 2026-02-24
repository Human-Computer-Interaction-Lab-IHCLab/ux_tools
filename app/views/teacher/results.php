<h1 class="text-2xl font-bold mb-4">Resultados globales</h1>
<form method="get" class="bg-white p-3 rounded mb-4 flex gap-2">
<select name="instance_id" class="border p-2"><?php foreach($instances as $i): ?><option value="<?= $i['id'] ?>" <?= $instanceId==$i['id']?'selected':'' ?>>#<?= $i['id'] ?> <?= e($i['title']) ?> (<?= e($i['team_name']) ?>)</option><?php endforeach; ?></select>
<button class="bg-blue-600 text-white px-3">Cargar</button></form>
<?php if($data): ?>
<pre class="bg-white p-3 rounded text-xs overflow-auto"><?= e(json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre>
<a class="text-blue-700" href="/teacher/results/export?instance_id=<?= $instanceId ?>&kind=assignments">Export CSV asignaciones</a> |
<a class="text-blue-700" href="/teacher/results/export?instance_id=<?= $instanceId ?>&kind=similarity">Export CSV similitud</a> |
<a class="text-blue-700" href="/teacher/results/export?instance_id=<?= $instanceId ?>&kind=tt">Export CSV tree</a>
<?php endif; ?>
