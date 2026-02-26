<div align="center">
  <div style="background: linear-gradient(135deg, #6366f1, #8b5cf6, #a78bfa); padding: 16px; border-radius: 12px; display: inline-block; margin-bottom: 20px; box-shadow: 0 0 20px rgba(99, 102, 241, 0.4);">
    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="2" y="6" width="20" height="12" rx="2"></rect>
      <path d="M6 12h4"></path>
      <path d="M8 10v4"></path>
      <circle cx="15" cy="13" r="1"></circle>
      <circle cx="18" cy="11" r="1"></circle>
    </svg>
  </div>
  <h1>Game Manager</h1>
  <p><strong>A dynamic game data manager for editing JSON configurations and collections</strong></p>
</div>

## Overview

Game Manager is a robust web application tailored for game developers to seamlessly manage, edit, and export their game data. Built with modern web technologies including **Laravel 8** and **Livewire**, it provides a responsive, dark-themed, and gaming-inspired UI to handle dynamic JSON collections and static data effortlessly.

## Key Features

- **🎮 Dynamic Collections Editor**: Define data schemas dynamically and create game entries (like Items, Loot Tables, Enemies, Skills) visually without touching code.
- **📄 Static JSON Editor**: Easily manage static or singleton data structures directly within the app.
- **📊 Spreadsheet View**: Manage large data sets effectively using an interactive spreadsheet-like interface.
- **📦 Data Import/Export**: One-click JSON import and export functionalities to sync data seamlessly with your actual game client/server setups.
- **👥 Multi-Game & Multi-User Support**: Work across multiple distinct game projects simultaneously, with defined user access permissions.
- **💅 Modern Gaming UI**: Designed with an aesthetics-first approach using sleek gradients, glassmorphism, and smooth animations.

## Tech Stack

- **Backend**: Laravel 8 (PHP 7.4/8.0)
- **Frontend**: Laravel Livewire, Vanilla CSS, Blade Components
- **Database**: MySQL / PostgreSQL
- **Asset Compilation**: Laravel Mix (Webpack)

## Getting Started

### Prerequisites
- PHP >= 7.3
- Composer
- Node.js & NPM
- MySQL/MariaDB

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd game-manager
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install Node Dependencies & Build Assets**
   ```bash
   npm install
   npm run dev
   ```

4. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Make sure to configure your `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in the `.env` file.*

5. **Run Database Migrations & Seeders**
   ```bash
   php artisan migrate --seed
   ```
   *The default seeder will provision initial collections and users to get you started.*

6. **Serve the Application**
   ```bash
   php artisan serve
   ```
   *You can now access the app locally via `http://localhost:8000` or your configured local server url.*

## UI Configuration & Customization
Game Manager runs entirely on a vanilla CSS design system located in our components.
To update colors, adjust the highly customizable CSS variables inside `resources/views/components/layouts/app.blade.php`:

```css
:root {
    --bg-primary: #0a0e1a;
    --accent-primary: #6366f1;
    /* ... */
}
```

## License
Open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
