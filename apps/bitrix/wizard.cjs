const { chromium } = require('playwright-core');
const base = 'http://cms:8080';
const adminPath = '/bitrix/admin/index.php?lang=ru';

async function checkAdmin(page) {
    await page.goto(base + adminPath, { waitUntil: 'commit' });
    const login = page.locator('input[name="USER_LOGIN"]:visible');
    const logout = page.getByText('Выйти', { exact: true }).first();
    await login.or(logout).first().waitFor();
    if (await login.isVisible()) {
        await login.fill('admin');
        const password = page.locator('input[name="USER_PASSWORD"]:visible');
        await password.fill(process.env.BX_ADMIN_PASSWORD);
        await password.press('Enter');
    }
    await logout.waitFor();
    await page.getByText('Рабочий стол', { exact: true }).first().waitFor();
}

async function install(page) {
    const response = await page.request.get(base + adminPath, { maxRedirects: 0 });
    if (response.status() !== 404) return;
    await page.goto(base + '/index.php');
    const field = name => page.locator(`[name="__wiz_${name}"]:visible`).first();
    const next = page.locator('[name="StepNext"]:visible');
    await next.click();
    await field('agree_license').check();
    await next.click();
    await field('lic_key_variant').uncheck();
    await next.click();
    await next.click();
    for (const [name, value] of Object.entries({
        host: 'db', user: 'bx', database: 'bx', password: process.env.BX_DB_PASSWORD,
    })) await field(name).fill(value);
    await next.click();
    await field('admin_password').waitFor({ timeout: 150000 });
    for (const [name, value] of Object.entries({
        login: 'admin', admin_password: process.env.BX_ADMIN_PASSWORD,
        admin_password_confirm: process.env.BX_ADMIN_PASSWORD, email: 'admin@example.invalid',
        user_name: 'Администратор', user_surname: 'EventHub',
    })) await field(name).fill(value);
    await next.click();
    await field('admin_password').waitFor({ state: 'hidden' });
}

(async () => {
    const browser = await chromium.launch({ executablePath: '/usr/bin/chromium', args: ['--disable-dev-shm-usage'] });
    try {
        const context = await browser.newContext();
        await context.route('**/*', route => new URL(route.request().url()).origin === base ? route.continue() : route.abort());
        const page = await context.newPage();
        page.setDefaultTimeout(20000);
        if (process.argv[2] === 'install') await install(page);
        await checkAdmin(page);
        console.log('Административный рабочий стол доступен.');
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error.message); process.exitCode = 1; });
