# Brand Theme Quick Reference Card

## 🎨 Quick Color Reference

```css
Primary:   #2563EB  (Royal Blue)    bg-primary-600
Secondary: #06B6D4  (Teal)          bg-secondary-500
Accent:    #7C3AED  (Indigo)        bg-accent-600
Deep Blue: #1E3A8A  (Dark Primary)  bg-primary-800
```

## 🚀 Quick Start

### Import Theme in Vue

```javascript
import theme from '@/Utils/theme.js';
```

### Use Gradients

```html
<!-- Background gradients -->
<div class="bg-gradient-primary">...</div>
<div class="bg-gradient-hero">...</div>
<div class="bg-gradient-accent">...</div>

<!-- Text gradients -->
<h1 class="text-gradient-primary">Title</h1>
<h2 class="text-gradient-hero">Subtitle</h2>
```

## 🎯 Common Patterns

### Button

```html
<button class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2.5 rounded-lg">
    Click Me
</button>
```

### Card

```html
<div class="bg-white rounded-lg shadow-md hover:shadow-lg p-6">
    Content
</div>
```

### Input

```html
<input class="border border-gray-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 rounded-lg px-4 py-2">
```

### Hero Section

```html
<section class="bg-gradient-hero min-h-screen">
    <div class="bg-hexagon-pattern opacity-20"></div>
    <!-- Content -->
</section>
```

## 📊 Chart Colors

```javascript
import theme from '@/Utils/theme.js';

const chartData = {
    datasets: [{
        backgroundColor: theme.chartTheme.backgroundColor[0],
        borderColor: theme.chartTheme.borderColor[0],
    }]
};
```

## 🌓 Dark Mode

```html
<div class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
    Auto dark mode
</div>
```

## 🔧 Build Commands

```bash
# Build assets
yarn build

# Watch for changes
yarn dev
```

## 📝 Color Classes

| Element | Class |
|---------|-------|
| Primary BG | `bg-primary-600` |
| Secondary BG | `bg-secondary-500` |
| Accent BG | `bg-accent-600` |
| Primary Text | `text-primary-600` |
| Primary Border | `border-primary-600` |
| Hover | `hover:bg-primary-700` |
| Active | `active:bg-primary-800` |
| Focus Ring | `focus:ring-primary-600` |

## 🎨 Gradient Reference

| Name | Usage |
|------|-------|
| `bg-gradient-primary` | Standard brand gradient |
| `bg-gradient-hero` | Landing pages, full spectrum |
| `bg-gradient-accent` | Premium features |
| `bg-gradient-subtle` | Card backgrounds |

## 💡 Tips

- Use `primary` for main actions
- Use `secondary` for supportive actions  
- Use `accent` for premium/special features
- Use `primary-800` for sidebar/headers
- Always add hover states
- Include focus rings for accessibility
- Test in dark mode

## 📚 Documentation

- Full Guide: `docs/BRAND_THEME_GUIDE.md`
- Migration: `docs/THEME_MIGRATION.md`
- Theme File: `resources/js/Utils/theme.js`
- CSS Config: `resources/css/app.css`
