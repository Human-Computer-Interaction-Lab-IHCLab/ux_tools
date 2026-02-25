<div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
<h1 class="text-2xl font-bold mb-2"><?= e($instance['title']) ?></h1>
<p class="mb-3"><?= nl2br(e($instance['instructions'] ?? '')) ?></p>
<form method="post" action="/p/<?= e($token) ?>/start" class="flex gap-2">
<input class="border p-2 flex-1" name="alias" placeholder="Alias opcional">
<button class="bg-blue-600 text-white px-4">Comenzar</button>
</form>
</div>
