# Landing Page Structure & Architecture

## Overview

Single-page marketing website delivered via a dedicated Laravel route (`/`), rendered through Inertia.js as a Vue 3 SPA. The page is publicly accessible and independent of tenant routing logic.

**Tech Stack:** Laravel 12 · Inertia.js · Vue 3 (Composition API) · TailwindCSS v4 · Vite · Yarn

---

## Route & Controller Setup

```
GET /  →  MarketingController@index  →  Inertia page: Marketing/Landing
```

The controller passes only static or lightly-dynamic props (pricing plans from DB, feature flags). No auth middleware is applied. The page is excluded from tenant resolution middleware.

---

## File Structure

```
resources/js/
├── Pages/
│   └── Marketing/
│       └── Landing.vue               # Root page component
├── Components/
│   └── Marketing/
│       ├── Navbar.vue
│       ├── Hero.vue
│       ├── TrustedBy.vue
│       ├── Features.vue
│       ├── Screenshots.vue
│       ├── Pricing.vue
│       ├── HowItWorks.vue
│       ├── DeveloperSection.vue
│       ├── Testimonials.vue
│       ├── Faq.vue
│       ├── CallToAction.vue
│       └── Footer.vue
```

---

## Section-by-Section Architecture

---

### 1. Navigation Bar

**Component:** `Navbar.vue`
**Layout:** Sticky top bar, full-width, blurred glass background on scroll (`backdrop-blur-md bg-white/80`).

**Left:** Logo + wordmark.
**Center:** Navigation links — Features · Pricing · How It Works · Docs · API.
**Right:** "Sign In" ghost button + "Start Free Trial" filled CTA button.

**Mobile:** Hamburger menu with a slide-down drawer. CTA remains visible.

**Design Notes:**
- Transitions from transparent to frosted-glass after 60px scroll.
- Active section highlighted via IntersectionObserver.
- Border-bottom appears only when scrolled.

---

### 2. Hero Section

**Component:** `Hero.vue`
**Layout:** Two-column (text left, visual right) on desktop. Stacked on mobile.

**Left Column:**
- Eyebrow label: `"Multi-Tenant SaaS Platform"` — small pill badge with subtle gradient border.
- H1 Headline: Large, bold (font-size clamp 2.5rem–4.5rem). Example: *"The complete infrastructure for your SaaS business."*
- Subheadline: 1–2 sentences describing the platform value. Muted text, 1.125rem.
- CTA cluster:
  - Primary: `"Start Building Free"` → `/register` (tenant registration).
  - Secondary: `"View Demo"` → scroll to Screenshots or open demo modal.
- Social proof micro-copy: `"Trusted by 500+ teams. No credit card required."`
- Tech stack badges (small logos): Laravel · Vue · Inertia · TailwindCSS.

**Right Column:**
- Browser chrome mockup (rounded window, traffic lights).
- Inside: Static or animated dashboard screenshot.
- Decorative: Radial gradient glow behind the mockup, floating card snippets (e.g., "New tenant registered", "MRR +12%") with subtle entrance animations.

**Design Notes:**
- Background: Near-white with a large subtle radial gradient (`from-indigo-50 via-white to-white`).
- Entrance animations: headline fades up, sub-text fades up with 100ms delay, CTA with 200ms delay, mockup slides in from right.
- Avoid heavy parallax — prefer CSS transitions for performance.

---

### 3. Trusted By / Social Proof

**Component:** `TrustedBy.vue`
**Layout:** Full-width strip, centered, minimal.

**Content:**
- Heading: `"Trusted by teams building SaaS products"` (small, muted).
- Row of 6–8 company logos in grayscale, 50% opacity. On hover: full opacity.
- Optional: auto-scrolling marquee on mobile using CSS animation.

**Design Notes:**
- Background: Slightly off-white or a very light gray strip.
- No distracting elements — this section builds credibility silently.
- Height: compact (80–100px).

---

### 4. Key Features

**Component:** `Features.vue`
**Layout:** Top heading block + 3-column grid (2-column on tablet, 1-column on mobile).

**Heading Block (centered):**
- H2: `"Everything you need to run a multi-tenant SaaS"`
- Subtext: 1 sentence expanding on it.

**Feature Cards (6 total):**

| # | Feature | Icon | Description |
|---|---------|------|-------------|
| 1 | Multi-Tenant Architecture | `BuildingOffice2Icon` | Isolated tenant databases with shared app logic. |
| 2 | Team Collaboration | `UsersIcon` | Invite teammates, assign roles, manage workspaces. |
| 3 | Role & Permission System | `ShieldCheckIcon` | Fine-grained RBAC across tenants and admin panel. |
| 4 | Subscription Plans | `CreditCardIcon` | Flexible plan tiers with usage limits and billing cycles. |
| 5 | Analytics Dashboards | `ChartBarIcon` | Real-time metrics for tenants and platform admins. |
| 6 | API Access | `CodeBracketIcon` | RESTful API with token-based authentication per tenant. |

**Card Design:**
- White card, soft shadow, 1px border (`border-gray-100`).
- Icon in a small colored square (indigo/violet tint).
- Feature title in semibold.
- 2-line description in muted text.
- Hover: slight lift (`translate-y-[-2px]`) and deeper shadow.

**Design Notes:**
- Section background: white.
- Cards animate in via scroll-triggered stagger using IntersectionObserver + CSS transitions.

---

### 5. Product Screenshots

**Component:** `Screenshots.vue`
**Layout:** Tabbed interface — left tab list, right preview panel. On mobile: vertical accordion or swipeable carousel.

**Tabs / Screens:**

| Tab Label | Content Preview |
|-----------|----------------|
| Admin Dashboard | Platform-level overview: revenue, tenants, active users, MRR chart. |
| Tenant Dashboard | Per-tenant overview: usage stats, recent activity, quick actions. |
| User Management | Data table with users, roles, invite flow, status badges. |
| Subscription & Billing | Plan cards, invoice history, upgrade/downgrade flow. |
| Analytics | Line/bar charts, date range picker, export button. |

**Tab Design:**
- Left sidebar (desktop): vertical list of tab buttons with active indicator bar on left side.
- Tab button: icon + label. Active state: indigo text + left border.
- Right panel: browser chrome mockup wrapping the screenshot image.
- Transition: cross-fade between screenshots (200ms opacity).

**Design Notes:**
- Screenshots are static images (PNG/WebP) placed in `public/images/screenshots/`.
- Section background: dark (`gray-950` or `slate-900`) for contrast — light screenshots pop on dark.
- Heading and subtext in white/gray on the dark background.

---

### 6. Pricing Plans

**Component:** `Pricing.vue`
**Layout:** Centered heading + 3-column card grid. Toggle for monthly/annual billing.

**Heading Block:**
- H2: `"Simple, transparent pricing"`
- Billing toggle: Monthly / Annually (annual shows `"Save 20%"` badge).

**Plan Cards (3 tiers):**

| Tier | Label | Highlight | CTA |
|------|-------|-----------|-----|
| Starter | Free | Basic features, 1 tenant | Get Started |
| Pro | $49/mo | Most popular (highlighted ring) | Start Free Trial |
| Enterprise | Custom | Unlimited tenants + SLA | Contact Sales |

**Card Design:**
- Standard card: white, soft shadow.
- Popular card: indigo background (or dark card), white text, `"Most Popular"` badge, slightly larger scale.
- Feature list: checkmark icon (`CheckIcon`) per feature line.
- CTA button: full-width at card bottom.

**Props from Controller:** Plan names, prices, features, and billing intervals passed from DB via Inertia props.

**Design Notes:**
- Annual price shown when toggle is active (computed in Vue from monthly price × 0.8 × 12).
- Animate price change with number transition on toggle.

---

### 7. How It Works

**Component:** `HowItWorks.vue`
**Layout:** Centered heading + horizontal 3-step flow with connecting line. Vertical on mobile.

**Steps:**

| Step | Title | Description |
|------|-------|-------------|
| 1 | Register Your Account | Sign up as a platform admin and configure your SaaS settings. |
| 2 | Invite Tenants | Create tenant workspaces and invite teams with role assignments. |
| 3 | Go Live | Launch your SaaS product — billing, analytics, and APIs are ready. |

**Visual Design:**
- Large step number circle (indigo filled) with connecting dashed horizontal line between steps.
- Icon or small illustration above each step number.
- Title in semibold, description in muted text below.

**Design Notes:**
- Section background: light gray (`gray-50`) for visual separation.
- Numbers animate in sequence on scroll.

---

### 8. Developer Friendly Section

**Component:** `DeveloperSection.vue`
**Layout:** Two-column. Left: text + bullet list. Right: code snippet block.

**Left Column:**
- Eyebrow: `"Built for Developers"`
- H2: `"A solid Laravel foundation you can extend"`
- Bullet points:
  - Clean Laravel 12 architecture with service layer pattern.
  - Inertia.js — no separate API needed for the frontend.
  - RESTful API (versioned) with Sanctum token auth per tenant.
  - Pluggable integrations: Stripe, Mailgun, S3, and more.
  - Artisan commands for tenant provisioning and database management.
  - Full Pest test suite included.

**Right Column:**
- Syntax-highlighted code block (using Shiki or Prism).
- Example: tenant creation via API or a sample Artisan command output.
- Dark terminal/editor aesthetic (`bg-gray-950`, `text-green-400` for code).

**Integration Logos:** Stripe · AWS S3 · Mailgun · GitHub · Slack (small, grayscale row below copy).

**Design Notes:**
- Section background: split — left white, right dark. Or full dark section for drama.
- Target audience: technical decision-makers. Credibility-focused.

---

### 9. Testimonials

**Component:** `Testimonials.vue`
**Layout:** Centered heading + 3-column card grid (or 2-column with larger cards).

**Card Content:**
- Quote text (2–4 sentences).
- Author name, title, company.
- Company logo or avatar image.
- 5-star rating row (optional).

**Design Notes:**
- Background: white or very light `indigo-50`.
- Cards: white, soft shadow, left border accent in indigo.
- On mobile: swipeable carousel (use `overflow-x-auto` + `snap-x`).
- Quotes use a large decorative `"` character as a visual element.

---

### 10. FAQ

**Component:** `Faq.vue`
**Layout:** Centered heading + single-column accordion list (max-width 700px).

**Suggested Questions:**

1. Is there a free trial available?
2. How does multi-tenancy work?
3. Can I use my own domain per tenant?
4. What payment methods are supported?
5. Is there an API for custom integrations?
6. Can I migrate from another platform?
7. What happens when I upgrade or downgrade a plan?
8. Do you offer support and SLAs?

**Accordion Design:**
- Each question: full-width row with question text + `ChevronDownIcon`.
- On click: answer expands with smooth height transition (`max-height` CSS transition).
- Only one open at a time (accordion, not multi-expand).
- Bottom border per item, no card shadow.

**Design Notes:**
- Background: white.
- Implemented with Vue `ref` for active index — no external library needed.

---

### 11. Call To Action (Bottom)

**Component:** `CallToAction.vue`
**Layout:** Full-width band, centered content.

**Content:**
- H2: `"Start building your SaaS today."`
- Subtext: `"Get started in minutes. No credit card required."`
- Two buttons: `"Create Free Account"` (primary, white on dark) · `"Talk to Sales"` (ghost).

**Design Notes:**
- Background: bold gradient (`from-indigo-600 to-violet-600`) or solid `indigo-700`.
- Text in white.
- Optional: subtle grid or dot pattern overlay for texture.
- This section mirrors the hero CTA to close the conversion loop.

---

### 12. Footer

**Component:** `Footer.vue`
**Layout:** 4-column grid (top) + single-row legal bar (bottom).

**Columns:**

| Column | Links |
|--------|-------|
| Product | Features · Pricing · Changelog · Roadmap |
| Developers | API Docs · GitHub · Integrations · Status |
| Company | About · Blog · Careers · Press |
| Legal | Privacy Policy · Terms of Service · Cookie Policy |

**Bottom Bar:**
- Left: `"© 2026 YourSaaS. All rights reserved."`
- Right: Social icons (GitHub · Twitter/X · LinkedIn).

**Design Notes:**
- Background: `gray-950` or `slate-900` (dark footer).
- Logo + tagline in first column (above link list).
- Text in `gray-400`, links hover to `white`.
- Divider line between columns and bottom bar.

---

## Visual Design System

### Color Palette

| Role | Value | Usage |
|------|-------|-------|
| Primary | `indigo-600` | CTAs, active states, accents |
| Primary Dark | `indigo-700` | Hover states |
| Secondary | `violet-500` | Gradient endpoints, badges |
| Neutral Dark | `gray-950` | Dark sections, footer, code blocks |
| Neutral Mid | `gray-600` | Body text |
| Neutral Light | `gray-100` | Section dividers, card borders |
| Background | `white` / `gray-50` | Page base |
| Success | `emerald-500` | Checkmarks, positive indicators |

### Typography

| Role | Style |
|------|-------|
| H1 (Hero) | Font weight 800, clamp(2.5rem, 5vw, 4.5rem) |
| H2 (Section) | Font weight 700, 2rem–2.5rem |
| H3 (Card) | Font weight 600, 1.125rem |
| Body | Font weight 400, 1rem, line-height 1.6 |
| Caption / Eyebrow | Font weight 500, 0.75rem, uppercase, letter-spacing wide |

**Font:** System font stack or `Inter` via Google Fonts / Bunny Fonts.

### Spacing Rhythm

- Section vertical padding: `py-24` (6rem) on desktop, `py-16` on mobile.
- Container max-width: `max-w-7xl mx-auto px-6`.
- Card gap: `gap-8` in grids.

### Elevation

| Level | Class |
|-------|-------|
| Flat | `shadow-none` + `border border-gray-100` |
| Raised | `shadow-md` |
| Floating | `shadow-xl` |
| Hero Mockup | `shadow-2xl` |

---

## Animation & Interaction Strategy

- **Scroll entrance:** IntersectionObserver triggers `opacity-0 translate-y-4` → `opacity-100 translate-y-0` with 300ms ease-out. Applied to section headings and cards.
- **Stagger:** Cards stagger with 75ms delay per item using CSS `transition-delay`.
- **Hover micro-interactions:** Cards lift (`-translate-y-0.5`), buttons scale subtly (`scale-[1.02]`).
- **No heavy JS animation libraries.** Use CSS transitions and Vue `<Transition>` for modal/accordion.
- **Billing toggle:** Vue `ref` + computed price with `<Transition>` for number swap.
- **Screenshot tabs:** Tab switching with opacity cross-fade.

---

## Performance Considerations

- All landing page components are lazy-loaded using Vue `defineAsyncComponent` except `Hero` and `Navbar`.
- Screenshots are served as WebP with `<img loading="lazy">`.
- Fonts are preloaded via `<link rel="preload">` in the Blade layout.
- The landing page Inertia page shares the default Blade layout but does **not** load authenticated-only JS chunks.
- `vite-plugin-compression` (gzip/brotli) should be enabled for production builds.

---

## SEO & Meta

Managed via `@inertiajs/vue3` `usePage` + a composable `useHead()` (or `@vueuse/head`):

```js
useHead({
  title: 'YourSaaS — Multi-Tenant SaaS Platform',
  meta: [
    { name: 'description', content: 'The complete infrastructure...' },
    { property: 'og:image', content: '/images/og-preview.png' },
  ],
})
```

- Open Graph tags for link previews.
- Canonical URL set to `/`.
- Structured data (`application/ld+json`) for Organization schema.

---

## Conversion Flow

```
Landing Page
    │
    ├── "Start Free Trial" CTA  →  /register  (tenant registration)
    ├── "Sign In"               →  /login
    ├── "View Demo"             →  #screenshots (smooth scroll)
    ├── "Talk to Sales"         →  /contact  or  mailto: link
    └── Pricing CTA             →  /register?plan={plan_slug}
```

- Registration route pre-fills the plan when `?plan=` is present.
- All CTAs share the same primary action: drive to `/register`.
