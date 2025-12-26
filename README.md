# Tsuyanki

> A modern, user-friendly spaced repetition system for effective learning

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![API Version](https://img.shields.io/badge/API-v1-green.svg)](docs/API.md)

---

## 🎯 About Tsuyanki

**Tsuyanki** is a web-based spaced repetition application for card-based learning that focuses on **ease of use without sacrificing flexibility**.

Unlike Anki, which is powerful but not intuitive for beginners, Tsuyanki clearly separates **content (notes)** and **practice (cards)**, provides explicit card templates, and offers a learning flow that can be used immediately without lengthy tutorials.

### 🎓 Philosophy

The main goal is **not to "replace Anki"**, but to:

- ✅ Lower the entry barrier for beginners
- ✅ Make SRS more usable and maintainable
- ✅ Provide a modern architecture ready for long-term development
- ✅ Offer flexibility without excessive complexity

---

## ✨ Key Features

### 🎴 Spaced Repetition System

- **SM-2 Algorithm** for optimal scheduling
- Review tracking with ease factor, interval, and repetition count
- Learning statistics (accuracy, streak, daily reviews)
- Due cards management with per-deck filtering

### 📚 Content Management

- **Clear Note vs Card Separation**: One note can generate multiple cards
- **Note Types**: Define field structures (expression, reading, meaning, etc.)
- **Card Templates**: Full control over front/back card display
- **Rich Media Support**: Audio, images, video attached to note fields
- **Tagging System**: Organize notes with tags

### 🔄 Anki Compatibility

- Import `.apkg` files (Anki deck packages)
- Preserve note structure, cards, media, and tags
- Easy migration from Anki to Tsuyanki

### 🎨 User Experience

- **Deck Forking**: Clone public decks from other users
- **Public/Private Decks**: Share or keep private
- RESTful API for integration with frontend/mobile apps
- Pagination, filtering, and search across all endpoints

### 🤖 AI-Ready Architecture

- Flexible data structure for AI-generated content
- API-first design for AI service integration
- Field schema supporting various content types

---

## 🏗️ Architecture

### Tech Stack

**Backend:**

- Laravel 11.x (PHP 8.2+)
- PostgreSQL (JSONB support for field storage)
- Laravel Sanctum (API authentication)

**Frontend:**

- Vue.js 3.x with Vite (for modern, fast development)
- JavaScript/TypeScript (configurable via jsconfig.json)

**Storage:**

- Local/Cloud storage for media files
- Structured JSONB fields for flexible content

**API:**

- RESTful API with Bearer token authentication
- Comprehensive error handling
- Pagination & filtering support

### Domain Model

```
User
 ├── Decks (owner)
 │    ├── Notes
 │    │    ├── Fields (JSONB)
 │    │    ├── Tags
 │    │    ├── Media Attachments
 │    │    └── Cards (generated from templates)
 │    └── Source Deck (for forks)
 │
 ├── Review States (per card)
 │    ├── Ease Factor
 │    ├── Interval
 │    ├── Repetition Count
 │    └── Due Date
 │
 └── Note Types
      └── Card Templates
           ├── Front Template
           └── Back Template
```

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.2 or higher
- Composer
- PostgreSQL 14+
- Node.js & NPM (for frontend)

### Installation

1. **Clone repository**

   ```bash
   git clone https://github.com/yourusername/tsuyanki.git
   cd tsuyanki
   ```

2. **Install backend dependencies**

   ```bash
   cd backend
   composer install
   ```

3. **Setup backend environment**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**

   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=tsuyanki
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run backend migrations**

   ```bash
   php artisan migrate
   ```

6. **Install frontend dependencies**

   ```bash
   cd ../frontend
   npm install
   ```

7. **Start development servers**

   ```bash
   # Backend
   cd ../backend
   php artisan serve

   # Frontend (in another terminal)
   cd ../frontend
   npm run dev
   ```

API will be available at `http://localhost:8000/api/v1`, frontend at `http://localhost:5173` (default Vite port).

---

## 📖 Documentation

### API Documentation

Full API documentation is available in [`docs/api-contract.md`](docs/api-contract.md)

**Quick Links:**

- [Authentication](docs/api-contract.md#1-authentication)
- [Decks Management](docs/api-contract.md#2-decks)
- [Notes & Cards](docs/api-contract.md#5-notes)
- [Review System](docs/api-contract.md#7-study--review-sm-2)
- [Media Upload](docs/api-contract.md#8-media)
- [Anki Import](docs/api-contract.md#10-import-anki)

### Database Schema

Full schema is available in [`docs/DATABASE.md`](docs/database.md)

**Core Tables:**

- `decks` - Learning decks (public/private)
- `note_types` - Field structure definitions
- `notes` - Content layer (one note → many cards)
- `card_templates` - Display templates for cards
- `cards` - Generated from notes + templates
- `review_states` - SM-2 algorithm state per user/card
- `media` & `note_media` - File attachments

---

## 🎮 Usage Examples

### 1. Register & Login

```bash
# Register
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "user@mail.com",
    "password": "secret123",
    "password_confirmation": "secret123"
  }'

# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@mail.com",
    "password": "secret123"
  }'
```

### 2. Create Deck & Add Notes

```bash
# Create deck
curl -X POST http://localhost:8000/api/v1/decks \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "JLPT N5 Vocabulary",
    "description": "Basic Japanese vocabulary",
    "is_public": false
  }'

# Add note (cards will be auto-generated)
curl -X POST http://localhost:8000/api/v1/decks/1/notes \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "note_type_id": 1,
    "fields": {
      "expression": "食べる",
      "reading": "たべる",
      "meaning": "to eat"
    },
    "tags": ["verb", "basic"]
  }'
```

### 3. Study Session

```bash
# Get due cards
curl -X GET "http://localhost:8000/api/v1/review/due?limit=10" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Submit review
curl -X POST http://localhost:8000/api/v1/review/101 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "quality": 4
  }'
```

### 4. Import Anki Deck

```bash
curl -X POST http://localhost:8000/api/v1/import/anki \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@jlpt_n5.apkg"
```

---

## 🛠️ Development

### Running Tests

```bash
cd backend
php artisan test
```

### Code Style

```bash
# Format code
./vendor/bin/pint

# Static analysis
./vendor/bin/phpstan analyse
```

### Seeding Test Data

```bash
cd backend
php artisan db:seed
```

---

## 🗺️ Roadmap

### Phase 1: Core Features ✅

- [x] User authentication (register, login, logout)
- [x] Deck CRUD operations
- [x] Note types & card templates
- [x] Notes & cards management
- [x] SM-2 review algorithm
- [x] Media upload & attachment
- [x] Anki import (.apkg)
- [x] Tags system

### Phase 2: Enhanced UX 🚧

- [ ] Frontend web application (Vue.js)
- [ ] Rich text editor for card templates
- [ ] Bulk operations (import CSV, batch edit)
- [ ] Advanced statistics & analytics
- [ ] Study mode customization (card order, review limits)
- [ ] Dark mode support

### Phase 3: Social & Collaboration

- [ ] Public deck marketplace
- [ ] User profiles & following
- [ ] Deck ratings & reviews
- [ ] Collaborative decks
- [ ] Study groups

---

## 🤝 Contributing

Contributions are very welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

### How to Contribute

1. Fork repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation
- Keep commits atomic and descriptive

---

## 📝 License

This project is licensed under the MIT License - see [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- Inspired by [Anki](https://apps.ankiweb.net/) - The powerful SRS that started it all
- SM-2 Algorithm by [SuperMemo](https://www.supermemo.com/)
- Laravel community for amazing ecosystem
- All contributors and early testers

---

## 📞 Contact & Support

- **Issues**: [GitHub Issues](https://github.com/yourusername/tsuyanki/issues)
- **Discussions**: [GitHub Discussions](https://github.com/yourusername/tsuyanki/discussions)
- **Email**: support@tsuyanki.app
- **Discord**: [Join our community](https://discord.gg/tsuyanki)

---

<div align="center">

**Made with ❤️ for learners everywhere**

[⭐ Star this repo](https://github.com/yourusername/tsuyanki) | [🐛 Report Bug](https://github.com/yourusername/tsuyanki/issues) | [💡 Request Feature](https://github.com/yourusername/tsuyanki/issues)

</div>
