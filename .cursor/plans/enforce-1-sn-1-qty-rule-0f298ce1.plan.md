<!-- 0f298ce1-3754-4770-88d0-832859c55fd0 25ab8a75-0eaa-45c8-be86-5e1678d758f2 -->
# Enhance Breadcrumb Function with Auto-Generation

## Changes Required

### 1. Update `breadcrumb()` function (app/Helpers/functions_helper.php)

   - Add auto-generation logic when `$data` is null or empty
   - Auto-generate from current route/controller/method if not manually set
   - Keep existing HTML output style (same structure)
   - Support both manual override (existing behavior) and auto-generation (new feature)

### 2. Add helper method in BaseController (app/Controllers/BaseController.php)

   - Add `setBreadcrumb(array $items)` method for easy manual override
   - Add `addBreadcrumb(string $title, string $url = '')` method to append items
   - These methods will set `$this->data['breadcrumb']` for manual control

### 3. Auto-generation logic

   - If `$data` is null/empty, generate breadcrumb from:
     - Home → baseURL
     - Module name → moduleURL (from currentModule)
     - Controller name (if not index)
     - Method name (if not index)
   - Use route segments to build breadcrumb path
   - Format: Home > Module > Controller > Method (current page)

## Implementation Details

- **Backward Compatibility**: Existing code that sets `$this->data['breadcrumb']` will continue to work
- **Auto-generation**: If breadcrumb is not set, it will auto-generate from route
- **Manual Override**: Controllers can use `setBreadcrumb()` or directly set `$this->data['breadcrumb']`
- **Style Preservation**: HTML output remains identical to current implementation
- **Flexibility**: Supports both array format (existing) and new helper methods

## Example Usage

```php
// Auto-generated (no code needed)
// Result: Home > Module Name > Controller > Method

// Manual override in controller:
$this->setBreadcrumb([
    'Home' => base_url(),
    'Products' => base_url('products'),
    'Detail' => ''
]);

// Or append:
$this->addBreadcrumb('Edit', '');
```

### To-dos

- [ ] Ensure amount field is properly cast to integer in both Sales.php and Agent/Sales.php before API call
- [ ] Fix grandTotal overwrite issue in Agent/Sales.php line 699 to use calculated value consistently
- [ ] Verify API payload structure matches specification exactly with all required fields
- [ ] Check if invoice number format needs to be changed to all integers (no INV prefix) as user requested