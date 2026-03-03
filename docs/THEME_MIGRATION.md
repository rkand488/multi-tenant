# Brand Theme Migration Script

## Automated Color Migration

This script helps migrate from generic Tailwind colors to the new brand theme.

### Step 1: Backup Your Files

```bash
# Create backup
git add -A
git commit -m "Backup before theme migration"
```

### Step 2: Run Color Replacements

```bash
# Navigate to project root
cd /Users/ranium/Documents/Sites/tenant

# Replace indigo with primary
find resources/js -type f -name "*.vue" -exec sed -i '' 's/bg-indigo-600/bg-primary-600/g' {} +
find resources/js -type f -name "*.vue" -exec sed -i '' 's/bg-indigo-700/bg-primary-700/g' {} +
find resources/js -type f -name "*.vue" -exec sed -i '' 's/bg-indigo-500/bg-primary-500/g' {} +
find resources/js -type f -name "*.vue" -exec sed -i '' 's/text-indigo-600/text-primary-600/g' {} +
find resources/js -type f -name "*.vue" -exec sed -i '' 's/text-indigo-700/text-primary-700/g' {} +
find resources/js -type f -name "*.vue" -exec sed -i '' 's/border-indigo-600/border-primary-600/g' {} +
find resources/js -type f -name "*.vue" -exec sed -i '' 's/hover:bg-indigo-700/hover:bg-primary-700/g' {} +
find resources/js -type f -name "*.vue" -exec sed -i '' 's/focus:ring-indigo-500/focus:ring-primary-600/g' {} +

# Replace cyan/teal with secondary
find resources/js -type f -name "*.vue" -exec sed -i '' 's/bg-cyan-500/bg-secondary-500/g' {} +
find resources/js -type f -name "*.vue" -exec sed -i '' 's/text-cyan-500/text-secondary-500/g' {} +
find resources/js -type f -name "*.vue" -exec sed -i '' 's/bg-teal-500/bg-secondary-500/g' {} +

# Replace purple with accent
find resources/js -type f -name "*.vue" -exec sed -i '' 's/bg-purple-600/bg-accent-600/g' {} +
find resources/js -type f -name "*.vue" -exec sed -i '' 's/text-purple-600/text-accent-600/g' {} +
```

### Step 3: Update Component Patterns

#### Update Buttons

```bash
# Find all button components
grep -r "bg-blue-" resources/js/Components/UI/Button.vue

# Manual update needed for Button.vue variants
```

#### Update Sidebar

```bash
# Update sidebar colors
grep -r "bg-gray-800\|bg-gray-900" resources/js/Components/Navigation/Sidebar.vue

# Replace with:
# bg-primary-800 for sidebar background
# bg-primary-700 for hover states
# bg-primary-900 for active states
```

### Step 4: Verify Changes

```bash
# Build assets
yarn build

# Check for any remaining old colors
grep -r "indigo-600" resources/js/ --exclude-dir=node_modules
grep -r "#6366f1" resources/js/ --exclude-dir=node_modules
grep -r "#8b5cf6" resources/js/ --exclude-dir=node_modules
```

### Step 5: Manual Updates Required

Some components need manual review:

#### 1. Login Page (`resources/js/Pages/Auth/Login.vue`)

```vue
<!-- Add gradient background -->
<template>
    <div class="min-h-screen bg-gradient-hero flex items-center justify-center">
        <!-- Login form -->
    </div>
</template>
```

#### 2. Home Page Hero (`resources/js/Pages/Marketing/Home.vue`)

```vue
<template>
    <section class="relative overflow-hidden bg-gradient-hero min-h-screen">
        <div class="absolute inset-0 bg-hexagon-pattern opacity-20"></div>
        <div class="relative z-10">
            <!-- Hero content -->
        </div>
    </section>
</template>
```

#### 3. Dashboard Header

```vue
<template>
    <header class="bg-gradient-primary text-white shadow-lg">
        <!-- Header content -->
    </header>
</template>
```

#### 4. Update Charts

```javascript
// In chart components, import theme
import theme from '@/Utils/theme.js';

// Update chart options
const chartOptions = {
    plugins: {
        tooltip: {
            backgroundColor: theme.chartTheme.tooltip.backgroundColor,
            titleColor: theme.chartTheme.tooltip.titleColor,
        }
    }
};
```

### Step 6: Test Everything

```bash
# Start dev server
yarn dev

# Check these pages:
# - / (home/landing)
# - /login
# - /register
# - /dashboard (tenant)
# - /admin (admin)
# - /admin/analytics
```

### Step 7: Commit Changes

```bash
git add -A
git commit -m "feat: implement hexagonal brand theme system

- Updated Tailwind CSS v4 theme configuration
- Created design tokens in theme.js
- Migrated components to brand colors
- Added gradient backgrounds
- Updated charts with brand palette"
```

---

## Manual Migration Checklist

### Components to Update

- [x] `app.css` - Theme configuration
- [x] `theme.js` - Design tokens
- [ ] `Button.vue` - Update color variants
- [ ] `Sidebar.vue` - Update to primary-800
- [ ] `Navbar.vue` - Update active states
- [ ] `Card.vue` - Update shadows
- [ ] `Modal.vue` - Update overlay
- [ ] `Table.vue` - Update borders
- [ ] `Badge.vue` - Update colors
- [ ] `Alert.vue` - Update variants

### Pages to Update

- [ ] `Login.vue` - Add hero gradient background
- [ ] `Register.vue` - Add hero gradient background
- [ ] `Home.vue` - Update hero section
- [ ] `Dashboard.vue` - Update header
- [ ] `Admin/Dashboard.vue` - Update header
- [ ] `Pricing.vue` - Highlight featured plan

### Charts to Update

- [ ] Dashboard charts - Use chartTheme colors
- [ ] Analytics charts - Use chartTheme colors
- [ ] Usage charts - Use chartTheme colors

---

## Color Mapping Reference

| Old Color | New Color | Usage |
|-----------|-----------|-------|
| `indigo-600` | `primary-600` | Buttons, links |
| `indigo-700` | `primary-700` | Hover states |
| `indigo-500` | `primary-500` | Light variants |
| `blue-600` | `primary-600` | Primary actions |
| `cyan-500` | `secondary-500` | Secondary actions |
| `teal-500` | `secondary-500` | Info states |
| `purple-600` | `accent-600` | Premium features |
| `violet-600` | `accent-600` | Accents |

---

## Testing Command

```bash
# Quick test all color references
echo "Checking for old color references..."
echo "Indigo references:"
grep -r "indigo-" resources/js/ --exclude-dir=node_modules | wc -l
echo "Old blue references:"
grep -r "blue-6" resources/js/ --exclude-dir=node_modules | wc -l
echo "Cyan references:"
grep -r "cyan-" resources/js/ --exclude-dir=node_modules | wc -l
echo "Purple references:"
grep -r "purple-" resources/js/ --exclude-dir=node_modules | wc -l
```

---

## Rollback Plan

If something goes wrong:

```bash
# Rollback to previous commit
git reset --hard HEAD~1

# Or restore specific file
git checkout HEAD~1 -- resources/css/app.css
```

---

**Migration Script v1.0.0**
**For:** Hexagonal Multi-Tenant Cube Brand Theme
**Last Updated:** March 3, 2026
