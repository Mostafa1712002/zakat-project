const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext({
    viewport: { width: 375, height: 812 },
    deviceScaleFactor: 2,
  });
  const page = await context.newPage();

  await page.goto('http://crm.test/', { waitUntil: 'networkidle' });

  // Wait for content to load
  await page.waitForTimeout(2000);

  // Take full page screenshot
  await page.screenshot({
    path: '/home/mostafa/www/crm/claudedocs/screenshot-mobile.png',
    fullPage: true
  });

  console.log('Screenshot saved to /home/mostafa/www/crm/claudedocs/screenshot-mobile.png');

  await browser.close();
})();
