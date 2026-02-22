# Requirements: Purchase Supplier-Product Filtering

## Overview
When creating a purchase invoice, selecting a supplier must filter the product dropdown to show ONLY products belonging to that supplier. No products from other suppliers should appear.

## User Stories

### US-001: Product Filtering by Supplier
**As a** purchase manager
**I want to** see only the selected supplier's products in the product dropdown
**So that** I don't accidentally order products from the wrong supplier

**Acceptance Criteria:**
- WHEN no supplier is selected THE SYSTEM SHALL show an empty product dropdown with a prompt to select a supplier first
- WHEN a supplier is selected THE SYSTEM SHALL fetch and display only that supplier's products
- WHEN the supplier is changed THE SYSTEM SHALL clear previously selected products and show the new supplier's products
- WHEN a new item row is added THE SYSTEM SHALL show only the current supplier's products in the new row

### US-002: Data Consistency
**As a** system administrator
**I want to** ensure product-supplier relationships use a single unified approach (supplier_id FK)
**So that** product counts and filtering are consistent across all pages

**Acceptance Criteria:**
- WHEN a purchase is saved THE SYSTEM SHALL NOT use the old pivot table (product_supplier)
- WHEN viewing supplier index THE SYSTEM SHALL show correct product counts matching actual supplier_id assignments
