# Tsuyanki API Contract

**Version:** v1  
**Base URL:** `/api/v1`  
**Authentication:** Bearer Token (JWT / Laravel Sanctum)

---

## 1. Authentication

### POST /auth/register

Registrasi user baru.

**Request Body:**

```json
{
  "name": "Maulana",
  "email": "user@mail.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

**Response 201:**

```json
{
  "user": {
    "id": 1,
    "name": "Maulana",
    "email": "user@mail.com"
  },
  "token": "jwt-token-here"
}
```

### POST /auth/login

Login user.

**Request Body:**

```json
{
  "email": "user@mail.com",
  "password": "secret123"
}
```

**Response 200:**

```json
{
  "user": {
    "id": 1,
    "name": "Maulana",
    "email": "user@mail.com"
  },
  "token": "jwt-token-here"
}
```

### POST /auth/logout

Logout dan revoke token.

**Response 200:**

```json
{
  "message": "Logged out successfully"
}
```

### GET /auth/me

Get current user info.

**Response 200:**

```json
{
  "id": 1,
  "name": "Maulana",
  "email": "user@mail.com",
  "created_at": "2025-01-01T00:00:00Z"
}
```

---

## 2. Decks

### GET /decks

List semua deck milik user atau public decks.

**Query Parameters:**

- `page` (integer, default: 1)
- `per_page` (integer, default: 20, max: 100)
- `sort` (string, default: "created_at", options: "created_at", "updated_at", "title")
- `order` (string, default: "desc", options: "asc", "desc")
- `is_public` (boolean, optional)
- `search` (string, optional) - search by title or description

**Response 200:**

```json
{
  "data": [
    {
      "id": 10,
      "title": "JLPT N5 Vocabulary",
      "description": "Basic Japanese vocabulary",
      "is_public": false,
      "owner_user_id": 1,
      "source_deck_id": null,
      "notes_count": 120,
      "cards_count": 240,
      "created_at": "2025-01-10T10:00:00Z",
      "updated_at": "2025-01-10T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 5,
    "last_page": 1
  }
}
```

### GET /decks/:deckId

Detail deck.

**Response 200:**

```json
{
  "id": 10,
  "title": "JLPT N5 Vocabulary",
  "description": "Basic Japanese vocabulary",
  "is_public": false,
  "owner_user_id": 1,
  "source_deck_id": null,
  "notes_count": 120,
  "cards_count": 240,
  "created_at": "2025-01-10T10:00:00Z",
  "updated_at": "2025-01-10T10:00:00Z"
}
```

### POST /decks

Membuat deck baru.

**Request Body:**

```json
{
  "title": "JLPT N5 Vocabulary",
  "description": "Basic Japanese vocabulary",
  "is_public": false
}
```

**Response 201:**

```json
{
  "id": 10,
  "title": "JLPT N5 Vocabulary",
  "description": "Basic Japanese vocabulary",
  "is_public": false,
  "owner_user_id": 1,
  "source_deck_id": null,
  "created_at": "2025-01-10T10:00:00Z",
  "updated_at": "2025-01-10T10:00:00Z"
}
```

### PUT /decks/:deckId

Update deck.

**Request Body:**

```json
{
  "title": "JLPT N5 Updated",
  "description": "Updated description",
  "is_public": true
}
```

**Response 200:**

```json
{
  "id": 10,
  "title": "JLPT N5 Updated",
  "description": "Updated description",
  "is_public": true,
  "owner_user_id": 1,
  "updated_at": "2025-01-11T10:00:00Z"
}
```

### DELETE /decks/:deckId

Hapus deck dan semua notes serta cards di dalamnya.

**Response 200:**

```json
{
  "message": "Deck deleted successfully"
}
```

### POST /decks/:deckId/fork

Fork deck publik.

**Request Body:**

```json
{
  "title": "JLPT N5 (My Version)",
  "description": "My personal copy"
}
```

**Response 201:**

```json
{
  "id": 11,
  "title": "JLPT N5 (My Version)",
  "description": "My personal copy",
  "is_public": false,
  "owner_user_id": 1,
  "source_deck_id": 10,
  "notes_count": 120,
  "cards_count": 240,
  "created_at": "2025-01-11T10:00:00Z",
  "updated_at": "2025-01-11T10:00:00Z"
}
```

---

## 3. Note Types

### GET /note-types

List semua note types.

**Query Parameters:**

- `page` (integer, default: 1)
- `per_page` (integer, default: 20)

**Response 200:**

```json
{
  "data": [
    {
      "id": 1,
      "name": "Japanese Vocabulary",
      "field_schema": {
        "fields": [
          {
            "name": "expression",
            "type": "text",
            "required": true
          },
          {
            "name": "reading",
            "type": "text",
            "required": false
          },
          {
            "name": "meaning",
            "type": "text",
            "required": true
          },
          {
            "name": "audio",
            "type": "media",
            "mime_types": ["audio/*"],
            "required": false
          }
        ]
      },
      "templates_count": 2,
      "created_at": "2025-01-01T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 3
  }
}
```

### GET /note-types/:noteTypeId

Detail note type.

**Response 200:**

```json
{
  "id": 1,
  "name": "Japanese Vocabulary",
  "field_schema": {
    "fields": [
      {
        "name": "expression",
        "type": "text",
        "required": true
      },
      {
        "name": "reading",
        "type": "text",
        "required": false
      },
      {
        "name": "meaning",
        "type": "text",
        "required": true
      },
      {
        "name": "audio",
        "type": "media",
        "mime_types": ["audio/*"],
        "required": false
      }
    ]
  },
  "templates_count": 2,
  "created_at": "2025-01-01T10:00:00Z"
}
```

### POST /note-types

Membuat note type.

**Request Body:**

```json
{
  "name": "Japanese Vocabulary",
  "field_schema": {
    "fields": [
      {
        "name": "expression",
        "type": "text",
        "required": true
      },
      {
        "name": "reading",
        "type": "text",
        "required": false
      },
      {
        "name": "meaning",
        "type": "text",
        "required": true
      },
      {
        "name": "audio",
        "type": "media",
        "mime_types": ["audio/*"],
        "required": false
      }
    ]
  }
}
```

**Response 201:**

```json
{
  "id": 1,
  "name": "Japanese Vocabulary",
  "field_schema": {
    "fields": [
      {
        "name": "expression",
        "type": "text",
        "required": true
      },
      {
        "name": "reading",
        "type": "text",
        "required": false
      },
      {
        "name": "meaning",
        "type": "text",
        "required": true
      },
      {
        "name": "audio",
        "type": "media",
        "mime_types": ["audio/*"],
        "required": false
      }
    ]
  },
  "created_at": "2025-01-01T10:00:00Z"
}
```

### PUT /note-types/:noteTypeId

Update note type.

**Request Body:**

```json
{
  "name": "Japanese Vocab Updated",
  "field_schema": {
    "fields": [
      {
        "name": "expression",
        "type": "text",
        "required": true
      },
      {
        "name": "meaning",
        "type": "text",
        "required": true
      }
    ]
  }
}
```

**Response 200:**

```json
{
  "id": 1,
  "name": "Japanese Vocab Updated",
  "field_schema": {
    "fields": [
      {
        "name": "expression",
        "type": "text",
        "required": true
      },
      {
        "name": "meaning",
        "type": "text",
        "required": true
      }
    ]
  },
  "updated_at": "2025-01-02T10:00:00Z"
}
```

### DELETE /note-types/:noteTypeId

Hapus note type (jika tidak ada notes yang menggunakannya).

**Response 200:**

```json
{
  "message": "Note type deleted successfully"
}
```

---

## 4. Card Templates

### GET /note-types/:noteTypeId/card-templates

List card templates untuk note type tertentu.

**Response 200:**

```json
{
  "data": [
    {
      "id": 5,
      "note_type_id": 1,
      "name": "JP to ID",
      "front_template": "{{expression}}",
      "back_template": "{{meaning}}",
      "created_at": "2025-01-01T10:00:00Z"
    },
    {
      "id": 6,
      "note_type_id": 1,
      "name": "ID to JP",
      "front_template": "{{meaning}}",
      "back_template": "{{expression}}",
      "created_at": "2025-01-01T10:00:00Z"
    }
  ]
}
```

### GET /card-templates/{templateId}

Detail card template.

**Response 200:**

```json
{
  "id": 5,
  "note_type_id": 1,
  "name": "JP to ID",
  "front_template": "{{expression}}",
  "back_template": "{{meaning}}",
  "created_at": "2025-01-01T10:00:00Z",
  "updated_at": "2025-01-01T10:00:00Z"
}
```

### POST /note-types/{noteTypeId}/card-templates

Membuat card template.

**Request Body:**

```json
{
  "name": "JP to ID",
  "front_template": "{{expression}}",
  "back_template": "{{meaning}}"
}
```

**Response 201:**

```json
{
  "id": 5,
  "note_type_id": 1,
  "name": "JP to ID",
  "front_template": "{{expression}}",
  "back_template": "{{meaning}}",
  "created_at": "2025-01-01T10:00:00Z"
}
```

### PUT /card-templates/{templateId}

Update card template.

**Request Body:**

```json
{
  "name": "JP → ID",
  "front_template": "{{expression}}\n{{reading}}",
  "back_template": "{{meaning}}"
}
```

**Response 200:**

```json
{
  "id": 5,
  "note_type_id": 1,
  "name": "JP → ID",
  "front_template": "{{expression}}\n{{reading}}",
  "back_template": "{{meaning}}",
  "updated_at": "2025-01-02T10:00:00Z"
}
```

### DELETE /card-templates/{templateId}

Hapus card template (akan menghapus semua cards yang menggunakan template ini).

**Response 200:**

```json
{
  "message": "Card template deleted successfully",
  "cards_deleted": 120
}
```

---

## 5. Notes

### GET /decks/{deckId}/notes

List notes dalam deck.

**Query Parameters:**

- `page` (integer, default: 1)
- `per_page` (integer, default: 20)
- `tag` (string, optional) - filter by tag name
- `search` (string, optional) - search in fields

**Response 200:**

```json
{
  "data": [
    {
      "id": 20,
      "deck_id": 10,
      "note_type_id": 1,
      "fields": {
        "expression": "食べる",
        "reading": "たべる",
        "meaning": "makan"
      },
      "tags": ["verb", "basic"],
      "cards_count": 2,
      "created_at": "2025-01-10T10:00:00Z",
      "updated_at": "2025-01-10T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 120
  }
}
```

### GET /notes/{noteId}

Detail note.

**Response 200:**

```json
{
  "id": 20,
  "deck_id": 10,
  "note_type_id": 1,
  "fields": {
    "expression": "食べる",
    "reading": "たべる",
    "meaning": "makan"
  },
  "tags": ["verb", "basic"],
  "media": [
    {
      "media_id": 30,
      "field_name": "audio",
      "storage_key": "media/audio/taberu.mp3",
      "url": "https://storage.example.com/media/audio/taberu.mp3"
    }
  ],
  "cards": [
    {
      "card_id": 101,
      "template_name": "JP to ID"
    },
    {
      "card_id": 102,
      "template_name": "ID to JP"
    }
  ],
  "created_at": "2025-01-10T10:00:00Z",
  "updated_at": "2025-01-10T10:00:00Z"
}
```

### POST /decks/{deckId}/notes

Membuat note baru. Cards akan dibuat otomatis berdasarkan card templates.

**Request Body:**

```json
{
  "note_type_id": 1,
  "fields": {
    "expression": "食べる",
    "reading": "たべる",
    "meaning": "makan"
  },
  "tags": ["verb", "basic"]
}
```

**Response 201:**

```json
{
  "note": {
    "id": 20,
    "deck_id": 10,
    "note_type_id": 1,
    "fields": {
      "expression": "食べる",
      "reading": "たべる",
      "meaning": "makan"
    },
    "tags": ["verb", "basic"],
    "created_at": "2025-01-10T10:00:00Z"
  },
  "cards_created": [
    {
      "card_id": 101,
      "template_name": "JP to ID"
    },
    {
      "card_id": 102,
      "template_name": "ID to JP"
    }
  ]
}
```

### PUT /notes/{noteId}

Update isi note.

**Request Body:**

```json
{
  "fields": {
    "meaning": "to eat"
  },
  "tags": ["verb", "basic", "food"]
}
```

**Response 200:**

```json
{
  "id": 20,
  "fields": {
    "expression": "食べる",
    "reading": "たべる",
    "meaning": "to eat"
  },
  "tags": ["verb", "basic", "food"],
  "updated_at": "2025-01-11T10:00:00Z"
}
```

### DELETE /notes/{noteId}

Hapus note dan semua cards terkait.

**Response 200:**

```json
{
  "message": "Note deleted successfully",
  "cards_deleted": 2
}
```

### POST /notes/{noteId}/regenerate-cards

Generate ulang cards dari template (berguna setelah update template).

**Request Body:**

```json
{
  "force": true
}
```

**Response 200:**

```json
{
  "note_id": 20,
  "cards_deleted": 2,
  "cards_created": 2
}
```

---

## 6. Cards

### GET /cards/{cardId}

Detail card beserta informasi review state.

**Response 200:**

```json
{
  "id": 101,
  "note_id": 20,
  "card_template_id": 5,
  "template_name": "JP to ID",
  "front_html": "食べる",
  "back_html": "makan",
  "review_state": {
    "ease_factor": 2.6,
    "interval": 6,
    "repetition": 3,
    "due_at": "2025-01-17T10:00:00Z",
    "last_reviewed_at": "2025-01-11T10:00:00Z"
  },
  "created_at": "2025-01-10T10:00:00Z"
}
```

---

## 7. Study & Review (SM-2)

### GET /review/due

Mendapatkan cards yang perlu direview hari ini.

**Query Parameters:**

- `deck_id` (integer, optional) - filter by deck
- `limit` (integer, default: 20, max: 100)
- `include_new` (boolean, default: true) - include cards that haven't been reviewed

**Response 200:**

```json
{
  "cards": [
    {
      "card_id": 101,
      "note_id": 20,
      "deck_id": 10,
      "deck_title": "JLPT N5 Vocabulary",
      "template_name": "JP to ID",
      "front_html": "食べる",
      "back_html": "makan",
      "review_state": {
        "ease_factor": 2.6,
        "interval": 6,
        "repetition": 3,
        "due_at": "2025-01-11T10:00:00Z"
      }
    }
  ],
  "summary": {
    "due_count": 15,
    "new_count": 5,
    "total": 20
  }
}
```

### GET /review/stats

Statistik review user.

**Query Parameters:**

- `deck_id` (integer, optional)
- `period` (string, default: "today", options: "today", "week", "month", "all")

**Response 200:**

```json
{
  "period": "today",
  "reviews_completed": 45,
  "cards_due": 15,
  "cards_new": 5,
  "accuracy": 85.5,
  "average_ease_factor": 2.45,
  "streak_days": 12
}
```

### POST /review/{cardId}

Submit hasil review card.

**Request Body:**

```json
{
  "quality": 4
}
```

_Quality values: 0-5 (SM-2 algorithm)_

- 0: Complete blackout
- 1: Incorrect, but familiar
- 2: Incorrect, but easy to recall
- 3: Correct, but difficult
- 4: Correct, with hesitation
- 5: Perfect recall

**Response 200:**

```json
{
  "card_id": 101,
  "ease_factor": 2.6,
  "interval": 6,
  "repetition": 3,
  "next_due_at": "2025-01-17T10:00:00Z",
  "message": "Good! See you in 6 days."
}
```

---

## 8. Media

### POST /media/upload

Upload file media.

**Request (multipart/form-data):**

```
file: taberu.mp3
```

**Limits:**

- Max file size: 10MB
- Allowed types: image/_, audio/_, video/\*

**Response 201:**

```json
{
  "media_id": 30,
  "storage_key": "media/audio/taberu.mp3",
  "original_name": "taberu.mp3",
  "mime_type": "audio/mpeg",
  "url": "https://storage.example.com/media/audio/taberu.mp3",
  "size_bytes": 245760,
  "created_at": "2025-01-10T10:00:00Z"
}
```

### GET /media/{mediaId}

Detail media.

**Response 200:**

```json
{
  "id": 30,
  "storage_key": "media/audio/taberu.mp3",
  "original_name": "taberu.mp3",
  "mime_type": "audio/mpeg",
  "url": "https://storage.example.com/media/audio/taberu.mp3",
  "size_bytes": 245760,
  "owner_user_id": 1,
  "created_at": "2025-01-10T10:00:00Z"
}
```

### POST /notes/{noteId}/media

Attach media ke note field.

**Request Body:**

```json
{
  "media_id": 30,
  "field_name": "audio"
}
```

**Response 200:**

```json
{
  "note_id": 20,
  "media_id": 30,
  "field_name": "audio",
  "url": "https://storage.example.com/media/audio/taberu.mp3"
}
```

### DELETE /notes/{noteId}/media/{mediaId}

Detach media dari note.

**Response 200:**

```json
{
  "message": "Media detached successfully"
}
```

### DELETE /media/{mediaId}

Hapus media (hanya jika tidak digunakan oleh note manapun).

**Response 200:**

```json
{
  "message": "Media deleted successfully"
}
```

---

## 9. Tags

### GET /tags

List semua tags.

**Query Parameters:**

- `search` (string, optional)
- `limit` (integer, default: 50)

**Response 200:**

```json
{
  "data": [
    {
      "id": 1,
      "name": "verb",
      "notes_count": 45
    },
    {
      "id": 2,
      "name": "basic",
      "notes_count": 120
    }
  ]
}
```

### POST /tags

Create new tag.

**Request Body:**

```json
{
  "name": "advanced"
}
```

**Response 201:**

```json
{
  "id": 5,
  "name": "advanced"
}
```

### PUT /tags/{tagId}

Update tag name.

**Request Body:**

```json
{
  "name": "advanced-vocab"
}
```

**Response 200:**

```json
{
  "id": 5,
  "name": "advanced-vocab"
}
```

### DELETE /tags/{tagId}

Hapus tag (akan menghapus relasi dengan notes).

**Response 200:**

```json
{
  "message": "Tag deleted successfully"
}
```

---

## 10. Import Anki

### POST /import/anki

Import file Anki (.apkg).

**Request (multipart/form-data):**

```
file: jlpt_n5.apkg
deck_title: JLPT N5 Imported (optional, default: original deck name)
```

**Response 201:**

```json
{
  "deck_id": 50,
  "deck_title": "JLPT N5 Vocabulary",
  "notes_imported": 1200,
  "cards_generated": 2400,
  "media_imported": 300,
  "tags_imported": 15,
  "warnings": ["3 media files failed to import"]
}
```

### GET /import/anki/{importId}/status

Check status import (untuk async processing).

**Response 200:**

```json
{
  "import_id": "abc123",
  "status": "processing",
  "progress": 45,
  "message": "Importing notes... 540/1200"
}
```

---

## Error Responses

### 400 Bad Request

```json
{
  "error": "Bad Request",
  "message": "Invalid input data",
  "errors": {
    "email": ["The email field is required"],
    "password": ["The password must be at least 8 characters"]
  }
}
```

### 401 Unauthorized

```json
{
  "error": "Unauthorized",
  "message": "Invalid or expired token"
}
```

### 403 Forbidden

```json
{
  "error": "Forbidden",
  "message": "You don't have permission to access this resource"
}
```

### 404 Not Found

```json
{
  "error": "Not Found",
  "message": "Deck not found"
}
```

### 422 Unprocessable Entity

```json
{
  "error": "Unprocessable Entity",
  "message": "Validation failed",
  "errors": {
    "note_type_id": ["The selected note type is invalid"],
    "fields.expression": ["The expression field is required"]
  }
}
```

### 429 Too Many Requests

```json
{
  "error": "Too Many Requests",
  "message": "Rate limit exceeded. Please try again later.",
  "retry_after": 60
}
```

### 500 Internal Server Error

```json
{
  "error": "Internal Server Error",
  "message": "An unexpected error occurred"
}
```
