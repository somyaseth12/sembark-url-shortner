# URL Shortener - Multi-Role Service

A Laravel-based URL shortener service with role-based access control, supporting multiple companies and three user roles: **SuperAdmin**, **Admin**, and **Member**.

## 📋 Features

✅ **Multi-Tenancy:** Support for multiple companies  
✅ **Role-Based Access Control:** Three roles with distinct permissions  
✅ **URL Shortening:** Generate and manage short URLs  
✅ **Public Redirection:** Short URLs publicly resolvable with click tracking  
✅ **Invitation System:** Role-based user invitation to companies  
✅ **Scope-Based Viewing:** Users see URLs based on their role and company  

## 🔐 User Roles & Permissions

| Feature | SuperAdmin | Admin | Member |
|---------|-----------|-------|--------|
| **Login/Logout** | ✅ | ✅ | ✅ |
| **Create Short URLs** | ❌ | ✅ | ✅ |
| **View All URLs** | ✅ Global | ✅ Own Company | ✅ Own Links Only |
| **Invite Admin** | ✅ (New Company) | ❌ | ❌ |
| **Invite Users** | ❌ | ✅ (Own Company) | ❌ |
| **Download CSV** | ✅ All URLs | ✅ Company URLs | ✅ Own URLs |

## 🛠️ Installation & Setup

### Prerequisites

- PHP 8.2+
- Composer
- Node.js & npm
- SQLite or MySQL

### Clone & Install

```bash
git clone <your-github-repo-url>
cd sembark-url-shortener
composer install
npm install
```

### Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials:

```env
DB_CONNECTION=sqlite
# or
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=url_shortener
DB_USERNAME=root
DB_PASSWORD=
```

### Database Setup

```bash
php artisan migrate:fresh --seed
```

This will:
- Create all database tables
- Seed initial roles (SuperAdmin, Admin, Member)
- Create a SuperAdmin user
- Create test data (2 companies with admin & member users)

### Default Test Credentials

After seeding, use these credentials to log in:

|Role         |Email id               |Password|

SuperAdmin	|superadmin@example.com |password
Admin |(Tech Innovators)|	admin1@tech-innovators.com  |password
Admin (Digital Solutions)|	admin2@digital-solutions.com
|password
Member (Tech Innovators)  |	member1@tech-innovators.com
|password


### Start Development Server

```bash
php artisan serve
```

Access the app at `http://127.0.0.1:8000`

## 📚 API Endpoints

### Authentication

- `POST /login` - User login
- `POST /logout` - User logout
- `POST /register` - User registration

### Dashboard Routes

- `GET /dashboard` - Main dashboard (redirects based on role)
- `GET /superadmin/dashboard` - SuperAdmin dashboard
- `GET /client-admin/dashboard` - Admin dashboard
- `GET /member/dashboard` - Member dashboard

### Short URL Management

- `GET /short-urls` - List short URLs (scoped by role)
- `GET /short-urls/create` - Show form to create short URL
- `POST /short-urls` - Create a new short URL
- `GET /short-urls/{id}/edit` - Edit short URL form
- `PUT /short-urls/{id}` - Update short URL
- `DELETE /short-urls/{id}` - Delete short URL
- `GET /download-urls` - Download URLs as CSV

### Public Routes

- `GET /{short_code}` - Redirect to original URL (public, increments clicks)

## 🗂️ Database Schema

### Companies Table
```sql
- id (PK)
- name (unique)
- domain (unique)
- created_at, updated_at
```

### Users Table
```sql
- id (PK)
- name
- email (unique)
- password
- role_id (FK to roles)
- company_id (FK to companies, nullable for SuperAdmin)
- created_at, updated_at
```

### Short URLs Table
```sql
- id (PK)
- user_id (FK to users)
- company_id (FK to companies)
- original_url
- short_code (unique)
- clicks (default: 0)
- created_at, updated_at
```

### Roles Table
```sql
- id (PK)
- name (unique): SuperAdmin, Admin, Member
- created_at, updated_at
```

## 🔍 Authorization Logic

### Viewing Short URLs

**SuperAdmin:** Can see all URLs from all companies  
**Admin:** Can see only URLs created in their own company  
**Member:** Can see only URLs they personally created  

### Creating Short URLs

- **SuperAdmin:** Cannot create short URLs
- **Admin:** Can create short URLs for their company
- **Member:** Can create short URLs (associated with their account)

### Managing Users

- **SuperAdmin:** Can invite Admins to create new companies
- **Admin:** Can invite Admins or Members to their own company
- **Member:** Cannot invite users

## 📝 Role-Based Routes

Routes are protected with middleware:

```php
// SuperAdmin only
Route::middleware(['auth', 'role:SuperAdmin'])->group(function () {
    // SuperAdmin routes
});

// Admin and Member (authenticated)
Route::middleware(['auth'])->group(function () {
    Route::resource('short-urls', ShortUrlController::class);
});

// Public redirection (no auth required)
Route::get('/{code}', [ShortUrlController::class, 'redirect']);
```

## 🧪 Testing

Run tests:

```bash
php artisan test
```

### Browser tests (Laravel Dusk)

We added a Dusk browser test (`tests/Browser/InvitationTest.php`) that asserts the AJAX invite shows a modal with the registration link.

To run Dusk locally:

1. Install Dusk (dev):

```powershell
composer require --dev laravel/dusk
php artisan dusk:install
```

2. Run Dusk:

```powershell
php artisan dusk
```

Notes:
- Dusk requires ChromeDriver; the installer will attempt to download a compatible binary.
- If you run into permissions or environment issues, refer to the Laravel Dusk docs.


### Manual Testing Workflow

1. **Log in as SuperAdmin**
   - Navigate to `/superadmin/dashboard`
   - Observe: Can view all URLs from all companies
   - Attempt: Create a short URL → Should get error

2. **Log in as Admin**
   - Navigate to `/client-admin/dashboard`
   - Observe: Can see only company URLs
   - Action: Create a short URL
   - Action: Download company URLs as CSV

3. **Log in as Member**
   - Navigate to `/member/dashboard`
   - Observe: Can see only own created URLs
   - Action: Create a short URL
   - Limitation: Cannot download all company URLs

4. **Test Public Redirection**
   - Create a short URL (e.g., `ABC123`)
   - Visit `http://127.0.0.1:8000/ABC123`
   - Verify: Redirects to original URL and increments click count

## 📦 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── ShortUrlController.php
│   │   ├── DashboardController.php
│   │   ├── InvitationController.php
│   │   └── Auth/
│   ├── Middleware/
│   │   └── RoleMiddleware.php
│   └── Requests/
├── Models/
│   ├── User.php
│   ├── ShortUrl.php
│   ├── Company.php
│   ├── Role.php
│   └── Invite.php
└── Policies/
    └── ShortUrlPolicy.php

database/
├── migrations/
├── seeders/
│   ├── RoleSeeder.php
│   ├── SuperAdminSeeder.php
│   └── TestDataSeeder.php
└── factories/

resources/views/
├── layouts/
├── superadmin/
├── clientadmin/
├── member/
└── urls/
    ├── index.blade.php
    ├── create.blade.php
    └── edit.blade.php

routes/
├── web.php
├── auth.php
└── console.php
```

## 🚀 Deployment

1. Push to GitHub repository
2. Deploy to hosting (Laravel Forge, Heroku, DigitalOcean, etc.)
3. Run `php artisan migrate:fresh --seed` on production
4. Set strong passwords for production users

## 📝 AI Usage Disclosure

This project was developed with assistance from AI tools:
- **Chatgpt** - help to understand project
- **GitHub Copilot** - Code debugging
- **Claude Haiku 4.5** - Architecture planning and documentation


## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🤝 Contributing

Contributions are welcome! Please fork the repository and submit a pull request.

## 📧 Support

For issues or questions, please open a GitHub issue.

---

**Last Updated:** December 7, 2025  
**Version:** 1.0.0

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
