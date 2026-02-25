<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UX Tools</title>
    <link rel="stylesheet" href="assets/tailwind.css">
</head>
<body class="bg-gray-100 text-gray-900">
<div class="max-w-6xl mx-auto p-4">
<?php if (current_user()): ?>
<div class="flex justify-between mb-4 bg-white p-3 rounded">
    <div>Sesión: <?= e(current_user()['name'] ?: current_user()['email']) ?> (<?= e(current_user()['role']) ?>)</div>
    <form method="post" action="/logout"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><button class="text-red-600">Salir</button></form>
</div>
<?php endif; ?>
