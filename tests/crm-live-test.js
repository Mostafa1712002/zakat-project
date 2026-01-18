const { chromium } = require('playwright');

const BASE_URL = 'https://rogence.newaves-systems.com';
const EMAIL = 'admin@crm.test';
const PASSWORD = 'password';

async function runTests() {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext();
    const page = await context.newPage();

    console.log('===========================================');
    console.log('     CRM LIVE TEST - rogence.newaves-systems.com');
    console.log('===========================================\n');

    const results = [];

    // Helper function
    async function testPage(name, url, expectedText = null) {
        try {
            const response = await page.goto(url, { waitUntil: 'networkidle', timeout: 30000 });
            const status = response.status();
            const passed = status === 200;

            if (expectedText) {
                const content = await page.content();
                const hasText = content.includes(expectedText);
                results.push({ name, url, status, passed: passed && hasText });
                console.log(`${passed && hasText ? '✅' : '❌'} ${name}: ${status}`);
            } else {
                results.push({ name, url, status, passed });
                console.log(`${passed ? '✅' : '❌'} ${name}: ${status}`);
            }
            return passed;
        } catch (error) {
            results.push({ name, url, status: 'ERROR', passed: false, error: error.message });
            console.log(`❌ ${name}: ERROR - ${error.message.substring(0, 50)}`);
            return false;
        }
    }

    try {
        // 1. Login
        console.log('🔐 LOGIN TEST');
        console.log('------------------------');
        await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle' });
        console.log('✅ Login page loaded');

        await page.fill('input[name="email"]', EMAIL);
        await page.fill('input[name="password"]', PASSWORD);
        await page.click('button[type="submit"]');
        await page.waitForURL(`${BASE_URL}/`, { timeout: 10000 });
        console.log('✅ Login successful\n');

        // 2. Dashboard
        console.log('📊 DASHBOARD');
        console.log('------------------------');
        await testPage('Dashboard', `${BASE_URL}/`);
        console.log('');

        // 3. Categories CRUD
        console.log('📁 CATEGORIES (الأقسام)');
        console.log('------------------------');
        await testPage('Categories Index', `${BASE_URL}/categories`);
        await testPage('Categories Create', `${BASE_URL}/categories/create`);
        console.log('');

        // 4. Products CRUD
        console.log('📦 PRODUCTS (الأصناف)');
        console.log('------------------------');
        await testPage('Products Index', `${BASE_URL}/products`);
        await testPage('Products Create', `${BASE_URL}/products/create`);
        console.log('');

        // 5. Customers CRUD
        console.log('👥 CUSTOMERS (العملاء)');
        console.log('------------------------');
        await testPage('Customers Index', `${BASE_URL}/customers`);
        await testPage('Customers Create', `${BASE_URL}/customers/create`);
        console.log('');

        // 6. Suppliers CRUD
        console.log('🏭 SUPPLIERS (الموردين)');
        console.log('------------------------');
        await testPage('Suppliers Index', `${BASE_URL}/suppliers`);
        await testPage('Suppliers Create', `${BASE_URL}/suppliers/create`);
        console.log('');

        // 7. Warehouses CRUD
        console.log('🏪 WAREHOUSES (المخازن)');
        console.log('------------------------');
        await testPage('Warehouses Index', `${BASE_URL}/warehouses`);
        await testPage('Warehouses Create', `${BASE_URL}/warehouses/create`);
        console.log('');

        // 8. Sales CRUD
        console.log('🛒 SALES (المبيعات)');
        console.log('------------------------');
        await testPage('Sales Index', `${BASE_URL}/sales`);
        await testPage('Sales Create', `${BASE_URL}/sales/create`);
        console.log('');

        // 9. Purchases CRUD
        console.log('📥 PURCHASES (المشتريات)');
        console.log('------------------------');
        await testPage('Purchases Index', `${BASE_URL}/purchases`);
        await testPage('Purchases Create', `${BASE_URL}/purchases/create`);
        console.log('');

        // 10. Invoices CRUD
        console.log('📄 INVOICES (الفواتير)');
        console.log('------------------------');
        await testPage('Invoices Index', `${BASE_URL}/invoices`);
        await testPage('Invoices Create', `${BASE_URL}/invoices/create`);
        console.log('');

        // 11. Sales Reps CRUD
        console.log('👤 SALES REPS (المندوبين)');
        console.log('------------------------');
        await testPage('Sales Reps Index', `${BASE_URL}/sales-reps`);
        await testPage('Sales Reps Create', `${BASE_URL}/sales-reps/create`);
        console.log('');

        // 12. Employees CRUD
        console.log('👨‍💼 EMPLOYEES (الموظفين)');
        console.log('------------------------');
        await testPage('Employees Index', `${BASE_URL}/employees`);
        await testPage('Employees Create', `${BASE_URL}/employees/create`);
        console.log('');

        // 13. Expenses CRUD
        console.log('💸 EXPENSES (المصروفات)');
        console.log('------------------------');
        await testPage('Expenses Index', `${BASE_URL}/expenses`);
        await testPage('Expenses Create', `${BASE_URL}/expenses/create`);
        console.log('');

        // 14. Reports
        console.log('📈 REPORTS (التقارير)');
        console.log('------------------------');
        await testPage('Reports Index', `${BASE_URL}/reports`);
        await testPage('Reports Profits', `${BASE_URL}/reports/profits`);
        await testPage('Reports Sales', `${BASE_URL}/reports/sales`);
        await testPage('Reports Inventory', `${BASE_URL}/reports/inventory`);
        console.log('');

        // 15. Settings
        console.log('⚙️ SETTINGS (الإعدادات)');
        console.log('------------------------');
        await testPage('Settings Index', `${BASE_URL}/settings`);
        await testPage('Settings Users', `${BASE_URL}/settings/users`);
        console.log('');

        // 16. Features (disabled tests)
        console.log('🚫 DISABLED FEATURES TEST');
        console.log('------------------------');
        const paymentsResponse = await page.goto(`${BASE_URL}/payments`, { waitUntil: 'networkidle' });
        const paymentsStatus = paymentsResponse.status();
        console.log(`${paymentsStatus === 403 ? '✅' : '❌'} Payments (disabled): ${paymentsStatus} ${paymentsStatus === 403 ? '(Feature disabled - correct!)' : ''}`);

        const dashboardResponse = await page.goto(`${BASE_URL}/my-dashboard`, { waitUntil: 'networkidle' });
        const dashboardStatus = dashboardResponse.status();
        console.log(`${dashboardStatus === 403 ? '✅' : '❌'} Sales Rep Dashboard (disabled): ${dashboardStatus} ${dashboardStatus === 403 ? '(Feature disabled - correct!)' : ''}`);

        const reportsResponse = await page.goto(`${BASE_URL}/reports/sales-reps`, { waitUntil: 'networkidle' });
        const reportsStatus = reportsResponse.status();
        console.log(`${reportsStatus === 403 ? '✅' : '❌'} Sales Reps Reports (disabled): ${reportsStatus} ${reportsStatus === 403 ? '(Feature disabled - correct!)' : ''}`);
        console.log('');

        // Summary
        const passed = results.filter(r => r.passed).length;
        const failed = results.filter(r => !r.passed).length;

        console.log('===========================================');
        console.log('                 SUMMARY');
        console.log('===========================================');
        console.log(`✅ Passed: ${passed}`);
        console.log(`❌ Failed: ${failed}`);
        console.log(`📊 Total:  ${results.length}`);
        console.log('===========================================');

    } catch (error) {
        console.error('Test failed:', error.message);
    } finally {
        await browser.close();
    }
}

runTests();
