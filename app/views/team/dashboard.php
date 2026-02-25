<h1 class="text-2xl font-bold mb-4">Panel de Equipo</h1>
<table class="w-full bg-white rounded text-sm">
<tr><th>ID</th><th>Título</th><th>Tipo</th><th>Estado</th><th></th></tr>
<?php foreach($instances as $i): ?>
<tr class="border-t"><td><?= $i['id'] ?></td><td><?= e($i['title']) ?></td><td><?= e($i['type']) ?></td><td><?= e($i['status']) ?></td><td><a class="text-blue-700" href="team/instance/<?= $i['id'] ?>">Editar</a> | <a class="text-indigo-700" href="team/instance/<?= $i['id'] ?>/results">Resultados</a></td></tr>
<?php endforeach; ?>
</table>
