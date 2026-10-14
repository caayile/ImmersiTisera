const { createDriver, loginAsDosen, visitAndSee } = require('./helpers');

/**
 * Cek semua menu utama dosen bisa dibuka (smoke per halaman).
 * Bukan uji isi form mendalam — itu lebih cocok di PHPUnit.
 */
async function run() {
  const driver = await createDriver();

  try {
    console.log('[2] Login + menu dosen');
    await loginAsDosen(driver);
    console.log('  OK — login dosen@imersi.id');

    const pages = [
      ['/participant/dashboard', 'Dashboard'],
      ['/participant/applications', 'Riwayat'],
      ['/participant/agreement', 'Perjanjian'],
      ['/participant/timeline', 'Checkpoint'],
      ['/participant/logbooks', 'Logbook'],
      ['/participant/mentoring', 'Mentoring'],
      ['/participant/final-report', 'Laporan'],
      ['/participant/evaluation', 'Hasil Nilai Magang'],
      ['/participant/collaboration', 'Kolaborasi'],
      ['/participant/notifications', 'Notifikasi'],
      ['/participant/settings', 'Pengaturan'],
    ];

    for (const [path, text] of pages) {
      await visitAndSee(driver, path, text);
    }

    console.log('  Selesai — semua menu dosen terbuka');
  } finally {
    await driver.quit();
  }
}

run().catch((error) => {
  console.error('GAGAL menu dosen:', error.message);
  process.exitCode = 1;
});
