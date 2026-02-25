<div class="bg-white p-4 rounded shadow" id="tree-testing" data-tasks='<?= e(json_encode($tasks)) ?>'>
<h1 class="text-xl font-bold mb-2">Tree Testing</h1>
<div id="task-box" class="mb-3"></div>
<div class="grid md:grid-cols-2 gap-3">
<div id="tree" class="border p-2 rounded">
<?php foreach($nodes as $n): ?><div class="node" data-id="<?= $n['id'] ?>" data-parent="<?= (int)$n['parent_id'] ?>">• <?= e($n['label']) ?></div><?php endforeach; ?>
</div>
<div>
<button id="next-task" class="bg-blue-600 text-white px-3 py-2">Guardar selección y siguiente</button>
<form id="tree-form" method="post" action="/p/<?= e($token) ?>/tree"><input type="hidden" name="answers" id="answers"></form>
</div>
</div>
</div>
