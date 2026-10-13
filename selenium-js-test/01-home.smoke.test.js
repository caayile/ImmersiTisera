const { By, until } = require('selenium-webdriver');
const { BASE_URL, createDriver } = require('./helpers');

async function run() {
  const driver = await createDriver();

  try {
    console.log('[1/3] Beranda');
    await driver.get(BASE_URL);
    await driver.wait(until.titleContains('Program TSU'), 10000);
    console.log('  OK —', await driver.getTitle());
  } finally {
    await driver.quit();
  }
}

run().catch((error) => {
  console.error('GAGAL beranda:', error.message);
  process.exitCode = 1;
});
