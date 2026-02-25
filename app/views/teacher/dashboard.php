<h1 class="text-2xl font-bold mb-4">Panel Profesor</h1>
<div class="grid md:grid-cols-2 gap-4">
<section class="bg-white p-4 rounded shadow">
<h2 class="font-semibold mb-2">Grupos y equipos</h2>
<form method="post" action="teacher/groups" class="flex gap-2 mb-2"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><input class="border p-2 flex-1" name="name" placeholder="Nuevo grupo"><button class="bg-blue-600 text-white px-3">Crear grupo</button></form>
<form method="post" action="teacher/teams" class="space-y-2"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><select name="group_id" class="border p-2 w-full"><?php foreach($groups as $g): ?><option value="<?= $g['id'] ?>"><?= e($g['name']) ?></option><?php endforeach; ?></select><input class="border p-2 w-full" name="name" placeholder="Nuevo equipo"><button class="bg-indigo-600 text-white px-3 py-1">Crear equipo</button></form>
<a class="text-blue-700 mt-2 inline-block" href="teacher/students">Gestionar estudiantes</a>
</section>
<section class="bg-white p-4 rounded shadow">
<h2 class="font-semibold mb-2">Plantillas y asignación</h2>
<form method="post" action="teacher/templates" class="space-y-2 mb-3"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><select name="type" class="border p-2 w-full"><option value="card_sorting">Card Sorting</option><option value="tree_testing">Tree Testing</option></select><input name="title" class="border p-2 w-full" placeholder="Título" required><textarea name="instructions" class="border p-2 w-full" placeholder="Instrucciones"></textarea><button class="bg-green-600 text-white px-3 py-1">Crear plantilla</button></form>
<form method="post" action="teacher/assign" class="space-y-2"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><select name="template_id" class="border p-2 w-full"><?php foreach($templates as $t): ?><option value="<?= $t['id'] ?>"><?= e($t['title']) ?> (<?= e($t['type']) ?>)</option><?php endforeach; ?></select><select name="group_id" class="border p-2 w-full"><?php foreach($groups as $g): ?><option value="<?= $g['id'] ?>"><?= e($g['name']) ?></option><?php endforeach; ?></select><button class="bg-purple-600 text-white px-3 py-1">Asignar a grupo</button></form>
</section>
</div>
<section class="bg-white p-4 mt-4 rounded shadow">
<h2 class="font-semibold">Instancias creadas</h2>
<table class="w-full text-sm"><tr><th>ID</th><th>Actividad</th><th>Equipo</th><th>Estado</th><th>Link participante</th></tr><?php foreach($instances as $i): ?><tr class="border-t"><td><?= $i['id'] ?></td><td><?= e($i['title']) ?></td><td><?= e($i['team_name']) ?></td><td><?= e($i['status']) ?></td><td><a class="text-blue-700" href="p/<?= e($i['participant_token']) ?>" target="_blank">/p/<?= e(substr($i['participant_token'],0,10)) ?>...</a></td></tr><?php endforeach; ?></table>
<a class="text-blue-700" href="teacher/results">Ver resultados globales</a>
</section>
