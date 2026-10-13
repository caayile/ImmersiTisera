const { createDriver, loginAsAdmin, visitAndSee } = require('./helpers');

async function run() {
  const driver = await createDriver();

  try {
    console.log('[4] Admin evaluasi + menu laporan');
    await loginAsAdmin(driver);
    console.log('  OK — login admin@imersi.id');

    await visitAndSee(driver, '/admin/evaluations', 'Laporan hasil evaluasi (spreadsheet)');
    await visitAndSee(driver, '/admin/evaluations', 'Ekspor CSV');
    await visitAndSee(driver, '/admin/reports', 'Laporan');
    await visitAndSee(driver, '/admin/monitoring', 'Logbook');

    console.log('  Selesai — laporan admin terbuka');
  } finally {
    await driver.quit();
  }
}

run().catch((error) => {
  console.error('GAGAL admin:', error.message);
  process.exitCode = 1;
});
