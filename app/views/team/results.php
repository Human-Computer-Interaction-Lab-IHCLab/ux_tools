<h1 class="text-xl font-bold mb-3">Resultados instancia #<?= $instance['id'] ?></h1>
<pre class="bg-white p-3 rounded text-xs overflow-auto"><?= e(json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre>
<a class="text-blue-700" href="/team/instance/<?= $instance['id'] ?>/export?kind=assignments">CSV asignaciones</a> |
<a class="text-blue-700" href="/team/instance/<?= $instance['id'] ?>/export?kind=similarity">CSV similitud</a> |
<a class="text-blue-700" href="/team/instance/<?= $instance['id'] ?>/export?kind=tt">CSV tree</a>
