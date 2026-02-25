<div class="max-w-md mx-auto bg-white rounded p-6 shadow">
    <h1 class="text-xl font-bold mb-4">Activar cuenta</h1>
    <p class="mb-4"><?= e($email ?? '') ?></p>
    <?php if (!empty($error)): ?><p class="text-red-600 mb-2"><?= e($error) ?></p><?php endif; ?>
    <form method="post" class="space-y-3">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input class="w-full border p-2" type="password" name="password" placeholder="Nueva contraseña" required>
        <button class="w-full bg-green-600 text-white p-2 rounded">Activar</button>
    </form>
</div>
