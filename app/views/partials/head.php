<?php $me=Auth::user(); ?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= APP_NAME ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{boxShadow:{card:'0 8px 24px rgba(0,0,0,.06)'}}}}</script>
<link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">
</head><body class="bg-white text-gray-900">
<header class="border-b"><div class="max-w-7xl mx-auto px-4 py-4 flex items-center gap-6">
<a href="<?= url('') ?>" class="font-semibold text-xl">Logo</a>
<nav class="hidden md:flex gap-6 text-sm"><a href="<?= url('') ?>" class="hover:underline">home</a><a class="hover:underline">about</a><a class="hover:underline">contact</a></nav>
<div class="ml-auto flex items-center gap-3">
<?php if($me): ?><span class="text-sm text-gray-600">Hi, <?= htmlspecialchars($me['name']) ?></span>
<a class="px-4 py-2 rounded-full bg-black text-white" href="<?= url('dashboard') ?>">dashboard</a>
<a class="px-3 py-2 rounded-full border" href="<?= url('logout') ?>">log out</a>
<?php else: ?>
<a class="px-3 py-2 rounded-full border" href="<?= url('login') ?>">log in</a>
<a class="px-4 py-2 rounded-full bg-black text-white" href="<?= url('register') ?>">register</a>
<?php endif; ?>
</div></div></header><main>
