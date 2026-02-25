<h1 class="text-2xl font-bold mb-4">Estudiantes por equipo</h1>
<form method="get" class="mb-4"><select class="border p-2" name="team_id"><?php foreach($teams as $t): ?><option value="<?= $t['id'] ?>" <?= $teamId==$t['id']?'selected':'' ?>><?= e($t['group_name'].' / '.$t['name']) ?></option><?php endforeach; ?></select><button class="bg-blue-600 text-white px-3 py-2">Ver</button></form>
<?php if($teamId): ?>
<section class="bg-white p-4 rounded shadow mb-4">
<form method="post" action="students/import" class="space-y-2"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="team_id" value="<?= $teamId ?>"><textarea name="csv_data" class="border p-2 w-full h-32" placeholder="Nombre,correo@x.com"></textarea><button class="bg-green-600 text-white px-3 py-1">Importar CSV pegado</button></form>
</section>
<section class="bg-white p-4 rounded shadow">
<table class="w-full text-sm"><tr><th>Nombre</th><th>Email</th><th>Activación</th><th>QR</th></tr>
<?php foreach($students as $s): $link='../activate/'.($s['activation_token'] ?? ''); ?>
<tr class="border-t"><td><?= e($s['name']) ?></td><td><?= e($s['email']) ?></td><td><a class="text-blue-700" href="<?= e($link) ?>" target="_blank"><?= e($link) ?></a></td><td><img alt="qr" src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?= urlencode((require __DIR__.'/../../config.php')['base_url'] . $link) ?>"></td></tr>
<?php endforeach; ?>
</table>
</section>
<?php endif; ?>
