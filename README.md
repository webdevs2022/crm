# Enterprise CRM — Education Platform (Full Project)

A complete, modular CRM for managing educational courses, lecture workflows, faculty, materials, contracts and payments.

---

## 🗂️ Project Structure

```
crm/
├── index.html                    ← Full UI (all 5 phases, 98KB, demo-ready)
├── .htaccess                     ← Apache URL rewriting
│
├── config/
│   └── database.php              ← PDO singleton DB connection
│
├── includes/
│   └── helpers.php               ← Shared: sanitize, paginate, jsonResponse
│
├── api/
│   └── index.php                 ← REST API router (all endpoints)
│
├── modules/
│   ├── courses/
│   │   ├── CourseModel.php       ← Course DB queries + pagination
│   │   └── CourseController.php  ← CRUD + stats + topics delegation
│   ├── topics/
│   │   ├── TopicModel.php        ← Topic DB queries + reorder
│   │   └── TopicController.php   ← CRUD + reorder
│   ├── workflow/
│   │   ├── WorkflowModel.php     ← Step init, toggle, progress
│   │   └── WorkflowController.php
│   ├── materials/
│   │   └── MaterialController.php ← CRUD + approve
│   ├── contracts/
│   │   └── ContractController.php ← CRUD + stats
│   ├── payments/
│   │   └── PaymentController.php  ← CRUD + monthly timeline
│   ├── dashboard/
│   │   └── DashboardController.php ← Summary, activity, breakdown, upcoming
│   └── faculty/
│       └── FacultyController.php  ← List, detail, create, update
│
└── database/
    ├── schema.sql                ← Phase 1 schema (Courses & Topics)
    └── schema_full.sql           ← Complete schema all phases + seed data
```

---

## ⚙️ Setup

### 1. Database
```bash
mysql -u root -p < database/schema_full.sql
```

### 2. Config
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'crm_db');
define('BASE_URL', 'http://localhost/crm');
```

### 3. Deploy
Place the `crm/` folder in your web server root (e.g. `htdocs/crm` for XAMPP).
Ensure `AllowOverride All` is set in Apache config.

### 4. Open
```
http://localhost/crm
```

> **Demo Mode:** `index.html` works entirely in-browser with built-in seed data — no server needed for preview. To use the real PHP API, wire `index.html` fetch calls to `/crm/api/...`.

---

## 📡 Full API Reference

Base: `http://localhost/crm/api`

### Courses
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/courses` | List (search, filter, paginate) |
| GET | `/courses/{id}` | Single course |
| GET | `/courses/stats` | Summary counts |
| GET | `/courses/{id}/topics` | Topics for course |
| POST | `/courses` | Create |
| PUT | `/courses/{id}` | Update |
| DELETE | `/courses/{id}` | Delete |

### Topics
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/topics?course_id=X` | List for course |
| GET | `/topics/{id}` | Single topic |
| GET | `/topics/{courseId}/stats` | Topic stats |
| POST | `/topics` | Create |
| PUT | `/topics/{id}` | Update |
| DELETE | `/topics/{id}` | Delete |
| POST | `/topics/{courseId}/reorder` | Reorder |

### Workflow
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/workflow` | Progress for all courses |
| GET | `/workflow/{topicId}` | Steps for topic |
| POST | `/workflow/{topicId}/init` | Initialize checklist |
| POST | `/workflow/{topicId}/toggle` | Toggle step done/undone |

### Materials
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/materials` | List (filter by course/topic/status/type) |
| GET | `/materials/{id}` | Single |
| GET | `/materials/stats` | Counts by status |
| POST | `/materials` | Create |
| PUT | `/materials/{id}` | Update / approve |
| DELETE | `/materials/{id}` | Delete |

### Contracts
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/contracts` | List (filter, search) |
| GET | `/contracts/{id}` | Single |
| GET | `/contracts/stats` | Total value, counts |
| POST | `/contracts` | Create |
| PUT | `/contracts/{id}` | Update |
| DELETE | `/contracts/{id}` | Delete |

### Payments
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/payments` | List (filter by faculty/contract/status) |
| GET | `/payments/{id}` | Single |
| GET | `/payments/stats` | Paid/pending totals |
| GET | `/payments/monthly` | Monthly payment timeline |
| POST | `/payments` | Create |
| PUT | `/payments/{id}` | Update / mark paid |
| DELETE | `/payments/{id}` | Delete |

### Dashboard
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/dashboard/summary` | All module counts + financial totals |
| GET | `/dashboard/activity` | Recent events |
| GET | `/dashboard/breakdown` | Topic status per course |
| GET | `/dashboard/timeline` | Monthly payment chart data |
| GET | `/dashboard/upcoming` | Upcoming scheduled lectures |

### Faculty
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/faculty` | All faculty with contract/payment totals |
| GET | `/faculty/{id}` | Full profile + contracts + payments + topics |
| POST | `/faculty` | Create |
| PUT | `/faculty/{id}` | Update |

---

## 🌱 Seed Data

| Entity | Count | Notes |
|--------|-------|-------|
| Users | 6 | 1 Admin, 1 Coordinator, 1 Employee, 3 Faculty |
| Courses | 5 | 3 Active, 2 Draft |
| Topics | 19 | Spread across 3 courses with varied statuses |
| Workflow Steps | 18 | Steps for topics 1, 3 (complete) and 4 (in progress) |
| Materials | 10 | Mix of PDF, doc, image, link |
| Contracts | 5 | 3 Active, 1 Completed, 1 Draft |
| Payments | 8 | Mix of advance/milestone/final; paid & pending |

**Default password for all users:** `password`

---

## 🎨 UI Modules

| Module | Features |
|--------|----------|
| **Dashboard** | 8 KPI cards, course progress bars, lecture status chart, payment summary, upcoming lectures |
| **Courses** | Card + table view, search/filter, CRUD, progress tracking |
| **Course Detail** | Tabbed topic list, progress header, per-topic workflow button |
| **Topics** | Full CRUD, faculty assign, type/status/schedule |
| **Workflow** | Per-topic checklist, step toggle with timestamp, progress bar |
| **Materials** | File/link tracker, approve/reject, filter by course/topic/type |
| **Contracts** | Contract CRUD, paid amount tracking, status pipeline |
| **Payments** | Invoice CRUD, mark-as-paid quick action, faculty filter |
| **Faculty** | Profile cards, earnings summary, detail modal with topics/contracts/payments |

---

## 🗺️ Phase Roadmap

| Phase | Module | Status |
|-------|--------|--------|
| ✅ 1 | Courses & Topics | Complete |
| ✅ 2 | Workflow Engine | Complete |
| ✅ 3 | Materials Tracking | Complete |
| ✅ 4 | Contracts & Payments | Complete |
| ✅ 5 | Dashboard & Reports | Complete |

---

## 🛠️ Tech Stack

- **Frontend:** HTML5, Bootstrap Icons, vanilla JS (demo-ready, no build step)
- **Backend:** PHP 8 (modular MVC)
- **Database:** MySQL/MariaDB with PDO
- **API:** RESTful JSON
