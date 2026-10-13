const { spawnSync } = require('child_process');
const path = require('path');

const tests = [
  '01-home.smoke.test.js',
  '02-dosen-menu.test.js',
  '03-mentor-menu.test.js',
  '04-admin-evaluasi.test.js',
];

let failed = false;

for (const file of tests) {
  console.log(`\n=== ${file} ===`);
  const result = spawnSync(process.execPath, [path.join(__dirname, file)], {
    stdio: 'inherit',
    env: process.env,
  });

  if (result.status !== 0) {
    failed = true;
    break;
  }
}

if (failed) {
  console.error('\nAda test yang gagal.');
  process.exit(1);
}

console.log('\nSemua Selenium smoke test lulus.');
