const { createDriver, loginAsMentor, visitAndSee } = require('./helpers');

async function run() {
  const driver = await createDriver();

  try {
    console.log('[3] Login + menu mentor');
    await loginAsMentor(driver);
    console.log('  OK — login mentor@imersi.id');

    const pages = [
      ['/mentor/dashboard', 'Dashboard'],
      ['/mentor/participants', 'Peserta'],
      ['/mentor/agreements', 'Perjanjian'],
      ['/mentor/logbooks', 'Logbook'],
      ['/mentor/penilaian', 'Penilaian'],
      ['/mentor/notifications', 'Notifikasi'],
    ];

    for (const [path, text] of pages) {
      await visitAndSee(driver, path, text);
    }

    console.log('  Selesai — menu mentor utama terbuka');
  } finally {
    await driver.quit();
  }
}

run().catch((error) => {
  console.error('GAGAL menu mentor:', error.message);
  process.exitCode = 1;
});
