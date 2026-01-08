#!/usr/bin/env node

const { exec } = require('child_process');
const { promisify } = require('util');
const fs = require('fs');
const path = require('path');

const execAsync = promisify(exec);
const GALLERY_DIR = path.join(__dirname, '../gallery');

async function analyzeWithGemini(imagePath, pageTitle) {
  const prompt = `Analyze this CRM system screenshot and provide:
1. A brief description (2-3 sentences) of what's shown
2. Key features visible in the interface
3. User experience highlights
4. Technical aspects if visible

Page Title: ${pageTitle}

Respond in Arabic (العربية) and keep it professional.`;

  try {
    const command = `gemini -p "${prompt}" < "${imagePath}"`;
    const { stdout } = await execAsync(command, { maxBuffer: 1024 * 1024 * 10 });
    return stdout.trim();
  } catch (error) {
    console.error(`Error analyzing ${imagePath}:`, error.message);
    return `تحليل ${pageTitle}`;
  }
}

async function enhanceGallery() {
  console.log('🤖 Enhancing gallery with Gemini AI...\n');

  const screenshots = [
    { file: '02-dashboard-1920x1080.png', title: 'لوحة التحكم الرئيسية' },
    { file: '03-invoices-list-1920x1080.png', title: 'قائمة الفواتير' },
    { file: '04-invoice-create-1920x1080.png', title: 'إنشاء فاتورة جديدة' },
    { file: '05-purchases-list-1920x1080.png', title: 'قائمة المشتريات' },
    { file: '07-employees-list-1920x1080.png', title: 'قائمة الموظفين' },
    { file: '09-customers-list-1920x1080.png', title: 'قائمة العملاء' },
    { file: '11-products-list-1920x1080.png', title: 'قائمة المنتجات' },
    { file: '18-reports-1920x1080.png', title: 'التقارير' },
  ];

  const descriptions = {};

  for (const screenshot of screenshots) {
    const imagePath = path.join(GALLERY_DIR, screenshot.file);
    if (!fs.existsSync(imagePath)) {
      console.log(`⚠️  Skipping ${screenshot.file} - file not found`);
      continue;
    }

    console.log(`🔍 Analyzing: ${screenshot.title}...`);
    const description = await analyzeWithGemini(imagePath, screenshot.title);
    descriptions[screenshot.file] = description;
    console.log(`✅ Done: ${screenshot.title}\n`);
  }

  // Save descriptions to JSON
  const outputPath = path.join(GALLERY_DIR, 'ai-descriptions.json');
  fs.writeFileSync(outputPath, JSON.stringify(descriptions, null, 2), 'utf8');
  console.log(`\n✅ AI descriptions saved to: ${outputPath}`);

  // Create enhanced README
  createEnhancedReadme(descriptions);
}

function createEnhancedReadme(descriptions) {
  const readme = `# 🤖 AI-Enhanced CRM Gallery

## Gemini AI Analysis

${Object.entries(descriptions).map(([file, desc]) => `
### ${file.replace('.png', '')}

${desc}

---
`).join('\n')}

## Generation Details

- **AI Model:** Google Gemini
- **Screenshots Analyzed:** ${Object.keys(descriptions).length}
- **Generated:** ${new Date().toLocaleString('ar-EG')}
`;

  fs.writeFileSync(path.join(GALLERY_DIR, 'AI_ANALYSIS.md'), readme);
  console.log('✅ Created AI_ANALYSIS.md');
}

// Run the enhancement
enhanceGallery().catch(console.error);
