# Green Ecochain Delivery Management System — Frontend

A modern, role-based delivery management web application for **Green Ecochain Co., Ltd**, built with Vue 3, Vite, and Tailwind CSS v4.

---

## Table of Contents

- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Prerequisites](#prerequisites)
- [Setup & Installation](#setup--installation)
- [Environment Variables](#environment-variables)
- [Running the App](#running-the-app)
- [Project Structure](#project-structure)
- [Features & Pages](#features--pages)
- [Role-Based Access](#role-based-access)
- [API Integration](#api-integration)
- [Design System](#design-system)
- [Build for Production](#build-for-production)

---

## Overview

This frontend connects to the Green Ecochain Laravel backend API and provides two distinct interfaces:

- **Admin Portal** — Full control over orders, drivers, routes, and analytics
- **Driver Portal** — Simplified view for drivers to manage their assigned deliveries

---

## Tech Stack

| Technology | Version | Purpose |
|---|---|---|
| Vue 3 | ^3.5 | UI framework (Composition API) |
| Vite | ^8.0 | Build tool & dev server |
| Tailwind CSS | ^4.2 | Utility-first styling |
| Vue Router | ^4 | Client-side routing |
| Pinia | ^2 | State management |
| Axios | ^1 | HTTP client |
| @heroicons/vue | ^2 | Icon library |
| TypeScript | ~6.0 | Type safety |

---

## Prerequisites

- **Node.js** v18 or higher
- **npm** v9 or higher
- The **backend Laravel API** running at `http://localhost:8000`

---

## Setup & Installation

### 1. Clone / navigate to the project

```bash
cd "c:\GEC Project\frontend-delivery-system"
```

### 2. Install dependencies

```bash
npm install
```

### 3. Configure environment

Copy the example env file and update as needed:

```bash
copy .env.example .env
```

Edit `.env`:

```env
VITE_API_URL=http://localhost:8000/api
```

### 4. Start the backend first

Make sure the Laravel backend is running:

```bash
# In the backend directory
php artisan serve
```

Then seed the database if not already done:

```bash
php artisan migrate --seed
```

---

## Running the App

### Development server

```bash
npm run dev
```

The app will be available at **http://localhost:5173**

### Build for production

```bash
npm run build
```

### Preview production build

```bash
npm run preview
```

---

## Environment Variables

| Variable | Default | Description |
|---|---|---|
| `VITE_API_URL` | `http://localhost:8000/api` | Base URL for the backend API |

---

## Project Structure

```
src/
├── api/                    # API service modules
│   ├── axios.ts            # Axios instance with interceptors
│   ├── auth.ts             # Auth endpoints (login, logout, me)
│   ├── orders.ts           # Delivery order endpoints
│   ├── drivers.ts          # Driver management endpoints
│   ├── routes.ts           # Route/navigation endpoints
│   └── reports.ts          # Reports & analytics endpoints
│
├── stores/                 # Pinia state stores
│   ├── auth.ts             # Authentication state (token, user, role)
│   ├── orders.ts           # Orders list, current order, pagination
│   └── drivers.ts          # Drivers list, current driver, live locations
│
├── router/
│   └── index.ts            # Vue Router with auth guards & role checks
│
├── layouts/
│   └── AppLayout.vue       # Main shell: collapsible sidebar + top bar
│
├── components/
│   ├── GecLogo.vue         # Green Ecochain SVG logo component
│   └── ui/
│       ├── StatCard.vue        # KPI metric card
│       ├── StatusBadge.vue     # Colored status pill (order/driver)
│       ├── PriorityBadge.vue   # Priority label (low/normal/high/urgent)
│       ├── LoadingSpinner.vue  # Animated loading indicator
│       ├── ConfirmModal.vue    # Reusable confirmation dialog
│       └── ToastNotification.vue # Toast message system
│
├── views/
│   ├── LoginView.vue           # Login page with demo credentials
│   ├── DashboardView.vue       # Admin dashboard with KPIs
│   ├── ReportsView.vue         # Orders report + driver performance
│   ├── DriverPortalView.vue    # Driver's delivery management page
│   ├── orders/
│   │   ├── OrdersView.vue      # Paginated orders list with filters
│   │   ├── OrderCreateView.vue # Create new delivery order form
│   │   └── OrderDetailView.vue # Order detail, assign driver, status updates
│   └── drivers/
│       ├── DriversView.vue     # Driver fleet grid with status tabs
│       ├── DriverCreateView.vue # Add new driver form
│       └── DriverDetailView.vue # Driver profile, performance, recent orders
│
├── App.vue                 # Root component
├── main.ts                 # App entry point
└── style.css               # Global styles + Tailwind + custom theme
```

---

## Features & Pages

### Login (`/login`)
- Email/password authentication via Sanctum token
- Auto-redirect based on role (admin → dashboard, driver → portal)
- Demo credential quick-fill buttons
- Show/hide password toggle

### Dashboard (`/dashboard`) — Admin only
- Live order counts: total, today, pending, in-transit, delivered, failed
- Driver fleet status breakdown with visual progress bars
- KPI panel: on-time rate, average delivery time, today's completions, delayed orders
- Quick action buttons for common tasks

### Orders (`/orders`) — Admin & Driver
- Paginated table with search, status, priority, and date range filters
- Color-coded status and priority badges
- Click any row to view full order details

### Order Detail (`/orders/:id`) — Admin & Driver
- Full order information: recipient, pickup/delivery addresses, timing
- Admin actions: assign driver (modal with available drivers), re-optimize route, cancel
- Driver actions: start delivery, mark delivered, report failure
- Route information: distance, estimated duration, optimization status
- Delivery stops timeline
- Complete status change history

### Create Order (`/orders/create`) — Admin only
- Recipient information
- Pickup and delivery addresses with optional GPS coordinates
- Priority selection (low / normal / high / urgent)
- Scheduled delivery time
- Special notes

### Drivers (`/drivers`) — Admin only
- Card grid layout with status filter tabs
- Shows vehicle info, license, order counts
- Click card to view driver profile

### Driver Detail (`/drivers/:id`) — Admin only
- Profile with vehicle details and current GPS location
- Inline status update buttons
- 30-day performance summary (orders, completion rate, on-time rate, distance)
- Recent orders list

### Add Driver (`/drivers/create`) — Admin only
- Creates both a user account and driver profile in one form
- Vehicle type, plate, and license number

### Reports (`/reports`) — Admin only
- **Orders Report tab**: date range + status filter, stats summary, full order table
- **Driver Performance tab**: per-driver metrics with daily breakdown table and performance score bar
- **Generate Reports tab**: trigger daily performance computation for all drivers

### Driver Portal (`/driver-portal`) — Driver only
- Personal delivery stats (total, in-transit, delivered, pending)
- Active deliveries highlighted with action buttons
- Status self-update (available / offline)
- Full order history with status filter

---

## Role-Based Access

| Route | Admin | Driver |
|---|---|---|
| `/dashboard` | ✅ | ❌ |
| `/orders` | ✅ | ✅ (own orders only) |
| `/orders/create` | ✅ | ❌ |
| `/orders/:id` | ✅ | ✅ (own orders only) |
| `/drivers` | ✅ | ❌ |
| `/drivers/create` | ✅ | ❌ |
| `/drivers/:id` | ✅ | ❌ |
| `/reports` | ✅ | ❌ |
| `/driver-portal` | ❌ | ✅ |

Route guards are enforced in `src/router/index.ts`. Unauthorized access redirects to the appropriate home page.

---

## API Integration

All API calls go through `src/api/axios.ts` which:
- Attaches the Bearer token from `localStorage` to every request
- Handles 401 responses globally by clearing auth state and redirecting to `/login`

### Auth Flow
1. `POST /api/auth/login` → receives `access_token` + user object
2. Token stored in `localStorage`
3. All subsequent requests include `Authorization: Bearer <token>`
4. `POST /api/auth/logout` → revokes token on backend

### Default Credentials (from seeder)

| Role | Email | Password |
|---|---|---|
| Admin | admin@gmail.com | password |
| Driver | driver@gmail.com | password |

---

## Design System

### Brand Colors

| Name | Hex | Usage |
|---|---|---|
| Brand Dark | `#0d3320` | Sidebar background, gradients |
| Brand | `#1a4731` | Sidebar hover, header gradients |
| Brand Green | `#3d9a3d` | Primary buttons, active states |
| Brand Light | `#5cb85c` | Accents, logo, badges |

### Status Colors

| Status | Color |
|---|---|
| Pending | Amber |
| Assigned | Blue |
| In Transit | Purple |
| Delivered | Green |
| Failed | Red |
| Cancelled | Slate |
| Available | Green |
| On Delivery | Blue |
| Offline | Slate |

### Typography
- Font: **Inter** (Google Fonts)
- Weights: 300, 400, 500, 600, 700, 800

---

## Build for Production

```bash
npm run build
```

Output is in the `dist/` folder. Deploy to any static hosting (Nginx, Apache, Vercel, Netlify, etc.).

### Nginx example config

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/frontend/dist;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location /api {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

---

## License

© 2026 Green Ecochain Co., Ltd. All rights reserved.
