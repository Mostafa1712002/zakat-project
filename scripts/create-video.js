#!/usr/bin/env node

const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const CRM_URL = 'http://crm.test';
const EMAIL = 'admin@crm.test';
const PASSWORD = 'password';
const OUTPUT_DIR = path.join(__dirname, '../gallery');
const VIDEO_PATH = path.join(OUTPUT_DIR, 'crm-walkthrough.webm');

// Ensure output directory exists
if (!fs.existsSync(OUTPUT_DIR)) {
  fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

const walkthrough = [
  { url: '/login', action: 'login', title: 'تسجيل الدخول', duration: 3000 },
  { url: '/', title: 'لوحة التحكم الرئيسية', duration: 5000, scroll: true },
  { url: '/invoices', title: 'قائمة الفواتير', duration: 4000, scroll: true },
  { url: '/invoices/create', title: 'إنشاء فاتورة جديدة', duration: 5000, scroll: true },
  { url: '/purchases', title: 'قائمة المشتريات', duration: 4000, scroll: true },
  { url: '/purchases/create', title: 'إنشاء فاتورة شراء', duration: 5000, scroll: true },
  { url: '/employees', title: 'قائمة الموظفين', duration: 4000, scroll: true },
  { url: '/employees/create', title: 'إضافة موظف جديد', duration: 4000, scroll: true },
  { url: '/customers', title: 'قائمة العملاء', duration: 4000, scroll: true },
  { url: '/products', title: 'قائمة المنتجات', duration: 4000, scroll: true },
  { url: '/suppliers', title: 'قائمة الموردين', duration: 4000, scroll: true },
  { url: '/warehouses', title: 'قائمة المخازن', duration: 4000 },
  { url: '/sales', title: 'قائمة المبيعات', duration: 4000, scroll: true },
  { url: '/expenses', title: 'قائمة المصروفات', duration: 4000 },
  { url: '/reports', title: 'التقارير', duration: 5000, scroll: true },
  { url: '/settings', title: 'الإعدادات', duration: 4000 },
  { url: '/', title: 'العودة للوحة التحكم', duration: 3000 },
];

async function smoothScroll(page) {
  await page.evaluate(async () => {
    const scrollHeight = document.documentElement.scrollHeight;
    const viewportHeight = window.innerHeight;
    const scrollSteps = Math.ceil(scrollHeight / 100);

    for (let i = 0; i < scrollSteps; i++) {
      window.scrollBy({
        top: 100,
        behavior: 'smooth'
      });
      await new Promise(resolve => setTimeout(resolve, 50));
    }

    // Scroll back to top
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
    await new Promise(resolve => setTimeout(resolve, 500));
  });
}

async function login(page) {
  console.log('🔐 Logging in...');
  await page.goto(`${CRM_URL}/login`);
  await page.waitForLoadState('networkidle');

  const emailInput = page.locator('input[name="email"]');
  const passwordInput = page.locator('input[name="password"]');
  const submitButton = page.locator('button[type="submit"]');

  // Type slowly for video
  await emailInput.type(EMAIL, { delay: 100 });
  await page.waitForTimeout(500);
  await passwordInput.type(PASSWORD, { delay: 100 });
  await page.waitForTimeout(500);
  await submitButton.click();
  await page.waitForLoadState('networkidle');

  console.log('✅ Logged in successfully!');
}

async function createVideo() {
  console.log('🎬 Starting CRM Video Walkthrough...\n');

  const browser = await chromium.launch({
    headless: false, // Show browser for recording
  });

  const context = await browser.newContext({
    locale: 'ar-EG',
    timezoneId: 'Africa/Cairo',
    viewport: { width: 1920, height: 1080 },
    recordVideo: {
      dir: OUTPUT_DIR,
      size: { width: 1920, height: 1080 },
    },
  });

  const page = await context.newPage();

  let step = 1;
  for (const item of walkthrough) {
    console.log(`📹 Step ${step}/${walkthrough.length}: ${item.title}`);

    if (item.action === 'login') {
      await login(page);
    } else {
      await page.goto(`${CRM_URL}${item.url}`, { waitUntil: 'networkidle' });
      await page.waitForTimeout(1000);

      if (item.scroll) {
        await smoothScroll(page);
      }

      await page.waitForTimeout(item.duration);
    }

    step++;
  }

  // Close and save video
  await page.close();
  await context.close();
  await browser.close();

  console.log('\n✅ Video recording complete!');
  console.log(`📁 Video saved to: ${OUTPUT_DIR}`);
  console.log('🎥 Check the gallery folder for the .webm video file');

  // Create info file
  createVideoInfo();
}

function createVideoInfo() {
  const info = `# CRM System Video Walkthrough

🎬 Complete video walkthrough of the CRM system

## Video Details

- **Duration:** ~${Math.ceil(walkthrough.reduce((sum, item) => sum + item.duration, 0) / 1000)} seconds
- **Resolution:** 1920x1080 (Full HD)
- **Format:** WebM
- **Pages Covered:** ${walkthrough.length}

## Pages in Video

${walkthrough.map((item, i) => `${i + 1}. ${item.title} (${item.url})`).join('\n')}

## Features Demonstrated

✅ User login and authentication
✅ Dashboard with live statistics
✅ Invoice creation and management
✅ Purchase order processing
✅ Employee management
✅ Customer database
✅ Product catalog
✅ Supplier management
✅ Warehouse inventory
✅ Sales tracking
✅ Expense management
✅ Reports and analytics
✅ System settings
✅ Arabic RTL interface
✅ Smooth scrolling and navigation

## Generated

- Date: ${new Date().toLocaleString('ar-EG')}
- Total Steps: ${walkthrough.length}

## How to View

The video is saved as \`.webm\` format. You can:
- Open it directly in Chrome/Firefox
- Convert to MP4 using: \`ffmpeg -i video.webm video.mp4\`
- Upload to YouTube or social media
`;

  fs.writeFileSync(path.join(OUTPUT_DIR, 'VIDEO_INFO.md'), info);
  console.log('✅ Created VIDEO_INFO.md');
}

// Run the script
createVideo().catch(error => {
  console.error('❌ Error:', error);
  process.exit(1);
});
