const { Builder, By, until, Key } = require('selenium-webdriver');
const chrome = require('selenium-webdriver/chrome');

const BASE_URL = process.env.APP_URL || 'http://localhost:8000';

async function createDriver() {
  const options = new chrome.Options();
  options.addArguments('--window-size=1400,900');
  if (process.env.HEADLESS === '1') {
    options.addArguments('--headless=new');
  }

  return new Builder()
    .forBrowser('chrome')
    .setChromeOptions(options)
    .build();
}

async function fill(driver, css, value) {
  const el = await driver.wait(until.elementLocated(By.css(css)), 10000);
  await driver.wait(until.elementIsVisible(el), 5000);
  await el.click();
  await el.sendKeys(Key.chord(Key.CONTROL, 'a'));
  await el.sendKeys(Key.BACK_SPACE);
  await el.sendKeys(value);
}

async function clickJs(driver, css) {
  const el = await driver.wait(until.elementLocated(By.css(css)), 10000);
  await driver.executeScript('arguments[0].scrollIntoView({block:"center"});', el);
  await driver.executeScript('arguments[0].click();', el);
}

async function dismissOverlays(driver) {
  // Splash / gambar dekoratif kadang menutupi tombol submit.
  await driver.executeScript(`
    document.querySelectorAll('#brand-splash, .brand-splash').forEach((el) => el.remove());
  `);
}

async function selectLoginRole(driver, role) {
  const radioId = role === 'mentor' ? '#role-mentor' : '#role-dosen';
  await clickJs(driver, radioId);
  // Radio is sr-only; updateRoleUI runs on change and sets form action.
  await driver.executeScript(`
    const radio = document.querySelector('${radioId}');
    if (radio && !radio.checked) {
      radio.checked = true;
      radio.dispatchEvent(new Event('change', { bubbles: true }));
    } else if (radio && typeof updateRoleUI === 'function') {
      updateRoleUI('${role === 'mentor' ? 'mentor' : 'dosen'}');
    }
  `);
}

async function loginAs(driver, { email, password, path = '/login/user', role = 'dosen' }) {
  await driver.get(`${BASE_URL}${path}`);
  await driver.wait(until.elementLocated(By.css('#form-login')), 10000);
  await dismissOverlays(driver);
  await selectLoginRole(driver, role);

  await fill(driver, '#form-login input[name="email"]', email);
  await fill(driver, '#form-login input[name="password"]', password);
  await dismissOverlays(driver);
  await clickJs(driver, '#form-login button[type="submit"]');

  try {
    await driver.wait(async () => {
      const url = await driver.getCurrentUrl();
      return !url.includes('/login');
    }, 15000);
  } catch (error) {
    const url = await driver.getCurrentUrl();
    let hint = '';
    try {
      const body = await driver.findElement(By.css('body')).getText();
      const match = body.match(/Akun mentor[\s\S]{0,80}|Email atau kata sandi[\s\S]{0,40}|bukan mentor[\s\S]{0,80}/);
      if (match) {
        hint = ` — ${match[0].replace(/\s+/g, ' ').trim()}`;
      }
    } catch (_) {
      // ignore
    }
    throw new Error(`Login gagal (masih di ${url})${hint}`);
  }
}

async function loginAsDosen(driver) {
  return loginAs(driver, {
    email: 'dosen@imersi.id',
    password: 'password',
    path: '/login/user',
    role: 'dosen',
  });
}

async function loginAsMentor(driver) {
  return loginAs(driver, {
    email: 'mentor@imersi.id',
    password: 'password',
    path: '/login/mentor',
    role: 'mentor',
  });
}

async function loginAsAdmin(driver) {
  return loginAs(driver, {
    email: 'admin@imersi.id',
    password: 'password',
    path: '/login/user',
    role: 'dosen',
  });
}

async function assertSee(driver, text, timeoutMs = 10000) {
  await driver.wait(async () => {
    const body = await driver.findElement(By.css('body')).getText();
    return body.includes(text);
  }, timeoutMs, `Teks tidak ditemukan: "${text}"`);
}

async function visitAndSee(driver, path, expectedText) {
  await driver.get(`${BASE_URL}${path}`);
  await assertSee(driver, expectedText);
  console.log(`  OK — ${path}`);
}

module.exports = {
  BASE_URL,
  createDriver,
  loginAsDosen,
  loginAsMentor,
  loginAsAdmin,
  assertSee,
  visitAndSee,
  dismissOverlays,
  clickJs,
};
