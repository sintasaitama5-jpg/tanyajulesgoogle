<!DOCTYPE html>
<html lang="<?= \App\Lang::current() ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= __t('login') ?> — <?= e(setting('app_name','InvoiceApp')) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 to-slate-700 flex items-center justify-center p-4">
<div class="w-full max-w-sm">
  <div class="text-center mb-8">
    <div class="text-white font-bold text-2xl"><?= e(setting('app_name','InvoiceApp')) ?></div>
    <div class="text-slate-400 text-sm mt-1">Professional Invoice System</div>
  </div>

  <div class="bg-white rounded-2xl shadow-xl p-8">
    <h1 class="text-slate-800 font-semibold text-lg mb-6"><?= __t('login_title') ?></h1>

    <?php if ($error): ?>
    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
      <?= e($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/login" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1"><?= __t('email') ?></label>
        <input type="email" name="email" required autofocus
               class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1"><?= __t('password') ?></label>
        <input type="password" name="password" required
               class="w-full border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500">
      </div>
      <button type="submit"
              class="w-full bg-slate-900 hover:bg-slate-700 text-white font-medium py-2.5 rounded-lg text-sm transition-colors">
        <?= __t('login') ?>
      </button>
    </form>
  </div>

  <div class="text-center mt-4">
    <a href="/lang?lang=<?= \App\Lang::other() ?>" class="text-slate-400 hover:text-white text-sm transition-colors">
      <?= \App\Lang::current() === 'id' ? 'Switch to English' : 'Ganti ke Indonesia' ?>
    </a>
  </div>
</div>
</body>
</html>
