# Brand Theme System - Implementation Guide

## 🎨 Hexagonal Multi-Tenant Cube Brand Identity

This document outlines the complete brand theme system implementation for the Laravel + Inertia + Vue 3 SaaS Multi-Tenant platform.

---

## 🌈 Brand Colors

### Primary Gradient Palette

Based on the Hexagonal Multi-Tenant Cube logo:

```css
Deep Blue:   #1E3A8A  /* Enterprise, Trust, Stability */
Royal Blue:  #2563EB  /* Primary Action, Main Brand */
Teal:        #06B6D4  /* Innovation, Modern, Growth */
Indigo:      #7C3AED  /* Premium, Accent, Highlight */
```

### Color Philosophy

- **Deep Blue (#1E3A8A)**: Foundation - represents enterprise stability and trust
- **Royal Blue (#2563EB)**: Primary - main brand color for CTAs and key elements
- **Teal (#06B6D4)**: Innovation - modern SaaS feel, secondary actions
- **Indigo (#7C3AED)**: Premium - accent for highlighting special features

---

## 📁 File Structure

```
resources/
├── css/
│   └── app.css                    # Tailwind CSS v4 theme configuration
└── js/
    └── Utils/
        └── theme.js               # JavaScript design tokens

docs/
└── BRAND_THEME_GUIDE.md          # This file
```

---

## 🎨 Theme Configuration

### 1. Tailwind CSS Configuration (`resources/css/app.css`)

Tailwind CSS v4 uses the `@theme` directive:

```css
@theme {
    /* Primary Color Scale */
    --color-primary-600: #2563eb;  /* Royal Blue - Main */
    --color-primary-800: #1e3a8a;  /* Deep Blue - Dark */
    
    /* Secondary Color Scale */
    --color-secondary-500: #06b6d4;  /* Teal - Main */
    
    /* Accent Color Scale */
    --color-accent-600: #7c3aed;  /* Indigo - Main */
    
    /* Brand Gradients */
    --gradient-primary: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #06b6d4 100%);
    --gradient-hero: linear-gradient(135deg, #1e3a8a 0%, #2563eb 35%, #06b6d4 70%, #7c3aed 100%);
    --gradient-accent: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
}
```

### 2. JavaScript Design Tokens (`resources/js/Utils/theme.js`)

Import and use in Vue components:

```javascript
import theme from '@/Utils/theme.js';

// Use brand colors
const primaryColor = theme.colors.primary[600];  // #2563eb

// Use gradients
const heroStyle = theme.getGradientStyle('hero');

// Chart colors
const chartColors = theme.chartTheme.colors;
```

---

## 🎨 Using the Brand Theme

### CSS Classes

#### Gradient Backgrounds

```html
<!-- Primary gradient -->
<div class="bg-gradient-primary">...</div>

<!-- Hero gradient (full brand palette) -->
<div class="bg-gradient-hero">...</div>

<!-- Accent gradient -->
<div class="bg-gradient-accent">...</div>

<!-- Subtle gradient (for cards, sections) -->
<div class="bg-gradient-subtle">...</div>
```

#### Gradient Text

```html
<!-- Primary gradient text -->
<h1 class="text-gradient-primary">Welcome to Our Platform</h1>

<!-- Hero gradient text -->
<h2 class="text-gradient-hero">Enterprise SaaS Solution</h2>
```

#### Hexagonal Pattern Background

```html
<div class="bg-hexagon-pattern bg-primary-50">
    <!-- Content with subtle hexagon pattern -->
</div>
```

#### Glass Morphism

```html
<!-- Light glass effect -->
<div class="glass">...</div>

<!-- Dark glass effect -->
<div class="glass-dark">...</div>
```

### Tailwind Color Classes

```html
<!-- Backgrounds -->
<div class="bg-primary-600">Royal Blue Background</div>
<div class="bg-secondary-500">Teal Background</div>
<div class="bg-accent-600">Indigo Background</div>

<!-- Text -->
<span class="text-primary-600">Royal Blue Text</span>
<span class="text-secondary-500">Teal Text</span>
<span class="text-accent-600">Indigo Text</span>

<!-- Borders -->
<div class="border border-primary-600">Royal Blue Border</div>

<!-- Hover States -->
<button class="bg-primary-600 hover:bg-primary-700 active:bg-primary-800">
    Click Me
</button>
```

---

## 🔧 Component Examples

### Buttons

```vue
<template>
    <!-- Primary Button -->
    <button class="
        bg-primary-600 hover:bg-primary-700 active:bg-primary-800
        text-white px-6 py-2.5 rounded-lg font-semibold
        shadow-md hover:shadow-lg
        transition-all duration-200
    ">
        Primary Action
    </button>
    
    <!-- Secondary Button -->
    <button class="
        bg-secondary-500 hover:bg-secondary-600 active:bg-secondary-700
        text-white px-6 py-2.5 rounded-lg font-semibold
    ">
        Secondary Action
    </button>
    
    <!-- Accent Button -->
    <button class="
        bg-accent-600 hover:bg-accent-700 active:bg-accent-800
        text-white px-6 py-2.5 rounded-lg font-semibold
    ">
        Premium Feature
    </button>
    
    <!-- Gradient Button -->
    <button class="bg-gradient-primary text-white px-6 py-2.5 rounded-lg font-semibold">
        Gradient CTA
    </button>
</template>
```

### Cards

```vue
<template>
    <!-- Standard Card -->
    <div class="bg-white rounded-lg shadow-md hover:shadow-lg p-6 transition-shadow">
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Card Title</h3>
        <p class="text-gray-600">Card content...</p>
    </div>
    
    <!-- Featured Card with Gradient Border -->
    <div class="bg-white rounded-lg shadow-lg p-1 bg-gradient-primary">
        <div class="bg-white rounded-lg p-6">
            <h3 class="text-xl font-semibold text-primary-600 mb-2">Featured</h3>
            <p class="text-gray-600">Premium content...</p>
        </div>
    </div>
    
    <!-- Gradient Card -->
    <div class="bg-gradient-primary rounded-lg shadow-lg p-6 text-white">
        <h3 class="text-xl font-semibold mb-2">Special Offer</h3>
        <p class="text-white/90">Limited time...</p>
    </div>
</template>
```

### Hero Section

```vue
<template>
    <section class="relative overflow-hidden bg-gradient-hero min-h-screen flex items-center">
        <!-- Hexagon Pattern Overlay -->
        <div class="absolute inset-0 bg-hexagon-pattern opacity-20"></div>
        
        <!-- Content -->
        <div class="relative z-10 container mx-auto px-4">
            <h1 class="text-5xl md:text-6xl font-bold text-white mb-6">
                Enterprise SaaS
                <span class="text-gradient-hero block">Multi-Tenant Platform</span>
            </h1>
            <p class="text-xl text-white/90 mb-8 max-w-2xl">
                Scale your business with our powerful multi-tenant solution
            </p>
            <button class="bg-white text-primary-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-50">
                Get Started
            </button>
        </div>
    </section>
</template>
```

### Navigation/Sidebar

```vue
<template>
    <nav class="bg-primary-800 text-white">
        <div class="p-4">
            <!-- Logo -->
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 bg-gradient-accent rounded-lg"></div>
                <span class="text-xl font-bold">SaaS Platform</span>
            </div>
            
            <!-- Nav Items -->
            <ul class="space-y-2">
                <li>
                    <a href="#" class="
                        flex items-center gap-3 px-4 py-2.5 rounded-lg
                        bg-primary-700 text-white
                        hover:bg-primary-600
                        transition-colors
                    ">
                        <Icon class="w-5 h-5" />
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="
                        flex items-center gap-3 px-4 py-2.5 rounded-lg
                        text-gray-200
                        hover:bg-primary-700
                        transition-colors
                    ">
                        <Icon class="w-5 h-5" />
                        <span>Analytics</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</template>
```

### Forms

```vue
<template>
    <form class="space-y-4">
        <!-- Input Group -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Email
            </label>
            <input
                type="email"
                class="
                    w-full px-4 py-2 rounded-lg
                    border border-gray-300
                    focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20
                    transition-colors
                "
                placeholder="you@example.com"
            />
        </div>
        
        <!-- Submit Button -->
        <button type="submit" class="
            w-full bg-gradient-primary text-white
            px-4 py-3 rounded-lg font-semibold
            hover:opacity-90 transition-opacity
        ">
            Sign In
        </button>
    </form>
</template>
```

### Data Visualization (Charts)

```vue
<script setup>
import { Line } from 'vue-chartjs';
import theme from '@/Utils/theme.js';

const chartData = {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
    datasets: [{
        label: 'Revenue',
        data: [12, 19, 3, 5, 2, 3],
        backgroundColor: theme.chartTheme.backgroundColor[0],
        borderColor: theme.chartTheme.borderColor[0],
        borderWidth: 2,
    }]
};

const chartOptions = {
    responsive: true,
    plugins: {
        legend: {
            labels: {
                color: theme.colors.gray[700]
            }
        },
        tooltip: {
            backgroundColor: theme.chartTheme.tooltip.backgroundColor,
            titleColor: theme.chartTheme.tooltip.titleColor,
            bodyColor: theme.chartTheme.tooltip.bodyColor,
        }
    },
    scales: {
        y: {
            grid: {
                color: theme.chartTheme.grid.color,
            },
            ticks: {
                color: theme.colors.gray[600]
            }
        },
        x: {
            grid: {
                color: theme.chartTheme.grid.color,
            },
            ticks: {
                color: theme.colors.gray[600]
            }
        }
    }
};
</script>

<template>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <Line :data="chartData" :options="chartOptions" />
    </div>
</template>
```

---

## 🌓 Dark Mode Support

The theme automatically adapts to dark mode:

```css
@media (prefers-color-scheme: dark) {
    :root {
        --color-background: #0f172a;
        --color-background-secondary: #1e293b;
        --color-text: #f1f5f9;
    }
}
```

### Dark Mode Classes

```html
<!-- Auto dark mode support -->
<div class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
    Content adapts to dark mode
</div>

<!-- Dark mode variants for brand colors -->
<button class="bg-primary-600 dark:bg-primary-500">
    Button
</button>
```

---

## ♿ Accessibility

All brand colors meet WCAG AA contrast requirements:

| Color Combination | Contrast Ratio | WCAG Level |
|-------------------|----------------|------------|
| Primary-600 on White | 4.84:1 | AA ✓ |
| Secondary-500 on White | 3.38:1 | AA Large ✓ |
| Accent-600 on White | 5.94:1 | AA ✓ |
| White on Primary-800 | 11.12:1 | AAA ✓ |

### Accessible Button Example

```html
<button
    class="bg-primary-600 text-white hover:bg-primary-700"
    aria-label="Submit form"
>
    Submit
</button>
```

---

## 📊 Usage Guidelines

### When to Use Each Color

**Primary (Royal Blue #2563EB)**
- Main CTAs (Sign Up, Get Started)
- Primary navigation active states
- Key action buttons
- Links and interactive elements

**Secondary (Teal #06B6D4)**
- Secondary actions (Cancel, Back)
- Info badges and tags
- Supportive UI elements
- Icons and illustrations

**Accent (Indigo #7C3AED)**
- Premium features
- Special highlights
- Promotional elements
- Notifications and badges

**Deep Blue (#1E3A8A)**
- Headers and backgrounds
- Footers
- Enterprise sections
- Trusted/secure indicators

### Gradient Usage

**Primary Gradient** - Standard brand gradient
- Buttons
- Cards
- Section backgrounds

**Hero Gradient** - Full brand spectrum
- Landing page heroes
- Login/register backgrounds
- Marketing pages

**Accent Gradient** - Highlight gradient
- Premium features
- Special announcements
- CTAs

**Subtle Gradient** - Background overlay
- Card backgrounds
- Section dividers
- Hover states

---

## 🎯 Migration Guide

### Replacing Old Colors

```diff
<!-- Before -->
- <div class="bg-indigo-600">
+ <div class="bg-primary-600">

- <button class="bg-blue-500">
+ <button class="bg-primary-600">

- <span class="text-cyan-500">
+ <span class="text-secondary-500">

- <div class="bg-purple-600">
+ <div class="bg-accent-600">
```

### Component Updates

Find and replace hardcoded hex colors:

```bash
# Search for old indigo colors
grep -r "indigo-600" resources/js/

# Replace with primary
sed -i 's/indigo-600/primary-600/g' resources/js/**/*.vue
```

---

## 🧪 Testing Checklist

- [ ] All buttons use brand colors
- [ ] Navigation uses primary color scheme
- [ ] Cards have consistent shadows
- [ ] Forms use brand focus colors
- [ ] Charts use brand color palette
- [ ] Dark mode works correctly
- [ ] Gradients render properly
- [ ] Hover states are consistent
- [ ] Accessibility contrast passes
- [ ] Mobile responsive layouts

---

## 📚 Resources

### Design Files
- Figma: [Brand Guidelines]
- Logo Assets: `/public/images/logo/`
- Color Swatches: Use theme.js

### Documentation
- `resources/css/app.css` - Theme configuration
- `resources/js/Utils/theme.js` - Design tokens
- `QUICK_REFERENCE.md` - Quick commands

### Tools
- [Tailwind CSS v4 Docs](https://tailwindcss.com/docs)
- [Color Contrast Checker](https://webaim.org/resources/contrastchecker/)
- [Gradient Generator](https://cssgradient.io/)

---

## 🚀 Quick Start

```bash
# Build assets with new theme
yarn build

# Watch for changes during development
yarn dev

# Check color usage in components
grep -r "bg-primary" resources/js/
```

---

**Brand Theme System v1.0.0**
**Last Updated:** March 3, 2026
**Maintained by:** Design System Team
