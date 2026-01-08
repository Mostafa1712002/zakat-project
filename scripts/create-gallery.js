#!/usr/bin/env node

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const CRM_URL = 'http://crm.test';
const EMAIL = 'admin@crm.test';
const PASSWORD = 'password';
const OUTPUT_DIR = path.join(__dirname, '../gallery');

// Ensure output directory exists
if (!fs.existsSync(OUTPUT_DIR)) {
  fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

const pages = [
  { name: '01-login', url: '/login', title: 'صفحة تسجيل الدخول' },
  { name: '02-dashboard', url: '/', title: 'لوحة التحكم الرئيسية', needsAuth: true },
  { name: '03-invoices-list', url: '/invoices', title: 'قائمة الفواتير', needsAuth: true },
  { name: '04-invoice-create', url: '/invoices/create', title: 'إنشاء فاتورة جديدة', needsAuth: true },
  { name: '05-purchases-list', url: '/purchases', title: 'قائمة المشتريات', needsAuth: true },
  { name: '06-purchase-create', url: '/purchases/create', title: 'إنشاء فاتورة شراء', needsAuth: true },
  { name: '07-employees-list', url: '/employees', title: 'قائمة الموظفين', needsAuth: true },
  { name: '08-employee-create', url: '/employees/create', title: 'إضافة موظف جديد', needsAuth: true },
  { name: '09-customers-list', url: '/customers', title: 'قائمة العملاء', needsAuth: true },
  { name: '10-customer-create', url: '/customers/create', title: 'إضافة عميل جديد', needsAuth: true },
  { name: '11-products-list', url: '/products', title: 'قائمة المنتجات', needsAuth: true },
  { name: '12-product-create', url: '/products/create', title: 'إضافة منتج جديد', needsAuth: true },
  { name: '13-suppliers-list', url: '/suppliers', title: 'قائمة الموردين', needsAuth: true },
  { name: '14-warehouses-list', url: '/warehouses', title: 'قائمة المخازن', needsAuth: true },
  { name: '15-categories-list', url: '/categories', title: 'قائمة الأقسام', needsAuth: true },
  { name: '16-sales-list', url: '/sales', title: 'قائمة المبيعات', needsAuth: true },
  { name: '17-expenses-list', url: '/expenses', title: 'قائمة المصروفات', needsAuth: true },
  { name: '18-reports', url: '/reports', title: 'التقارير', needsAuth: true },
  { name: '19-settings', url: '/settings', title: 'الإعدادات', needsAuth: true },
];

async function login(page) {
  console.log('🔐 Logging in...');
  await page.goto(`${CRM_URL}/login`);
  await page.waitForLoadState('networkidle');

  const emailInput = page.locator('input[name="email"]');
  const passwordInput = page.locator('input[name="password"]');
  const submitButton = page.locator('button[type="submit"]');

  await emailInput.fill(EMAIL);
  await passwordInput.fill(PASSWORD);
  await submitButton.click();
  await page.waitForLoadState('networkidle');

  console.log('✅ Logged in successfully!');
}

async function captureScreenshot(page, pageInfo, viewportSize = { width: 1920, height: 1080 }) {
  try {
    const filename = `${pageInfo.name}-${viewportSize.width}x${viewportSize.height}.png`;
    const filepath = path.join(OUTPUT_DIR, filename);

    console.log(`📸 Capturing: ${pageInfo.title} (${viewportSize.width}x${viewportSize.height})`);

    await page.setViewportSize(viewportSize);
    await page.goto(`${CRM_URL}${pageInfo.url}`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(1000); // Wait for animations

    await page.screenshot({
      path: filepath,
      fullPage: true,
    });

    console.log(`✅ Saved: ${filename}`);
    return filepath;
  } catch (error) {
    console.error(`❌ Error capturing ${pageInfo.name}:`, error.message);
    return null;
  }
}

async function createGallery() {
  console.log('🎬 Starting CRM Gallery Creation...\n');

  const browser = await chromium.launch({
    headless: true,
  });

  const context = await browser.newContext({
    locale: 'ar-EG',
    timezoneId: 'Africa/Cairo',
  });

  const page = await context.newPage();

  const screenshots = [];
  let isLoggedIn = false;

  // Capture screenshots for different viewport sizes
  const viewports = [
    { width: 1920, height: 1080, name: 'Desktop' },
    { width: 1366, height: 768, name: 'Laptop' },
    { width: 768, height: 1024, name: 'Tablet' },
    { width: 375, height: 812, name: 'Mobile' },
  ];

  for (const pageInfo of pages) {
    // Login if needed
    if (pageInfo.needsAuth && !isLoggedIn) {
      await login(page);
      isLoggedIn = true;
    }

    // Capture for each viewport
    for (const viewport of viewports) {
      const filepath = await captureScreenshot(page, pageInfo, viewport);
      if (filepath) {
        screenshots.push({
          page: pageInfo.name,
          title: pageInfo.title,
          viewport: viewport.name,
          file: filepath,
        });
      }
    }

    console.log(''); // Empty line for readability
  }

  await browser.close();

  // Create index.html gallery
  createHTMLGallery(screenshots);

  // Create README.md
  createReadme(screenshots);

  console.log('\n✅ Gallery creation complete!');
  console.log(`📁 Output directory: ${OUTPUT_DIR}`);
  console.log(`📸 Total screenshots: ${screenshots.length}`);
  console.log(`🌐 Open gallery/index.html in your browser to view`);
}

function createHTMLGallery(screenshots) {
  const html = `<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معرض صور نظام CRM</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            direction: rtl;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            text-align: center;
            color: #667eea;
            margin-bottom: 1rem;
            font-size: 2.5rem;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 2rem;
        }
        .filter-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 0.75rem 1.5rem;
            background: #f0f0f0;
            border: 2px solid transparent;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
        }
        .filter-btn:hover { background: #e0e0e0; }
        .filter-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .gallery-item {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .gallery-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            cursor: pointer;
        }
        .gallery-item-info {
            padding: 1rem;
        }
        .gallery-item-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }
        .gallery-item-meta {
            color: #666;
            font-size: 0.875rem;
        }
        .viewport-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: #667eea;
            color: white;
            border-radius: 12px;
            font-size: 0.75rem;
            margin-top: 0.5rem;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.9);
            z-index: 1000;
            padding: 2rem;
        }
        .modal.active { display: flex; align-items: center; justify-content: center; }
        .modal-content {
            max-width: 95%;
            max-height: 95%;
            position: relative;
        }
        .modal-content img {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
        }
        .modal-close {
            position: absolute;
            top: -40px;
            right: 0;
            background: white;
            color: #333;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.5rem;
        }
        .stats {
            text-align: center;
            padding: 1rem;
            background: #f9f9f9;
            border-radius: 10px;
            margin-top: 2rem;
        }
        .stats strong { color: #667eea; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📸 معرض صور نظام CRM</h1>
        <p class="subtitle">نظام إدارة علاقات العملاء المتكامل</p>

        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">الكل</button>
            <button class="filter-btn" data-filter="Desktop">سطح المكتب</button>
            <button class="filter-btn" data-filter="Laptop">اللابتوب</button>
            <button class="filter-btn" data-filter="Tablet">التابلت</button>
            <button class="filter-btn" data-filter="Mobile">الموبايل</button>
        </div>

        <div class="gallery">
            ${screenshots.map(screenshot => `
                <div class="gallery-item" data-viewport="${screenshot.viewport}">
                    <img src="${path.basename(screenshot.file)}" alt="${screenshot.title}" onclick="openModal('${path.basename(screenshot.file)}')">
                    <div class="gallery-item-info">
                        <div class="gallery-item-title">${screenshot.title}</div>
                        <div class="gallery-item-meta">${screenshot.page}</div>
                        <span class="viewport-badge">${screenshot.viewport}</span>
                    </div>
                </div>
            `).join('')}
        </div>

        <div class="stats">
            <strong>${screenshots.length}</strong> صورة تم التقاطها من
            <strong>${pages.length}</strong> صفحة مختلفة على
            <strong>4</strong> أحجام شاشات
        </div>
    </div>

    <div class="modal" id="modal" onclick="closeModal()">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()">✕</button>
            <img id="modalImage" src="" alt="">
        </div>
    </div>

    <script>
        // Filter functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const filter = btn.dataset.filter;
                document.querySelectorAll('.gallery-item').forEach(item => {
                    if (filter === 'all' || item.dataset.viewport === filter) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        // Modal functionality
        function openModal(imageSrc) {
            document.getElementById('modal').classList.add('active');
            document.getElementById('modalImage').src = imageSrc;
        }

        function closeModal() {
            document.getElementById('modal').classList.remove('active');
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });
    </script>
</body>
</html>`;

  fs.writeFileSync(path.join(OUTPUT_DIR, 'index.html'), html);
  console.log('✅ Created gallery/index.html');
}

function createReadme(screenshots) {
  const readme = `# CRM System Gallery

📸 **${screenshots.length} screenshots** captured from **${pages.length} pages**

## Pages Captured

${pages.map(p => `- ${p.title} (${p.url})`).join('\n')}

## Viewport Sizes

- 🖥️ Desktop: 1920x1080
- 💻 Laptop: 1366x768
- 📱 Tablet: 768x1024
- 📱 Mobile: 375x812

## How to View

Open \`index.html\` in your browser to view the interactive gallery.

## Features Showcased

✅ Dashboard with real-time statistics
✅ Invoice management with print view
✅ Purchase orders with dynamic items
✅ Employee management with search/filter
✅ Customer management
✅ Product catalog
✅ Supplier management
✅ Warehouse inventory
✅ Sales tracking
✅ Expense management
✅ Reports and analytics
✅ System settings
✅ Arabic RTL interface
✅ Responsive design (Desktop, Tablet, Mobile)

## Generated

- Date: ${new Date().toLocaleString('ar-EG')}
- Total Screenshots: ${screenshots.length}
`;

  fs.writeFileSync(path.join(OUTPUT_DIR, 'README.md'), readme);
  console.log('✅ Created gallery/README.md');
}

// Run the script
createGallery().catch(error => {
  console.error('❌ Error:', error);
  process.exit(1);
});
