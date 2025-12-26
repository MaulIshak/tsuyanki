// ===================================
// Core Domain: Flashcards & Learning
// ===================================

Table decks {
id integer [pk, increment]
owner_user_id integer [ref: > users.id]
title varchar
description text [note: 'nullable']
is_public boolean [default: false]
source_deck_id integer [ref: > decks.id, note: 'Self-reference for sub-decks or forks']
created_at timestamp
updated_at timestamp
}

Table note_types {
id integer [pk, increment]
name varchar
field_schema jsonb [note: 'Structure definition, e.g., fields: [expression, reading]']
created_at timestamp
updated_at timestamp
}

Table notes {
id integer [pk, increment]
deck_id integer [ref: > decks.id]
note_type_id integer [ref: > note_types.id]
fields jsonb [note: 'Actual content, e.g., {expression: "Taberu"}']
created_at timestamp
updated_at timestamp
}

Table card_templates {
id integer [pk, increment]
note_type_id integer [ref: > note_types.id]
name varchar
front_template text
back_template text
created_at timestamp
updated_at timestamp
}

Table cards {
id integer [pk, increment]
note_id integer [ref: > notes.id]
card_template_id integer [ref: > card_templates.id]
created_at timestamp
updated_at timestamp
}

Table review_states {
id integer [pk, increment]
user_id integer [ref: > users.id]
card_id integer [ref: > cards.id]
ease_factor float [default: 2.5]
interval integer [default: 0, note: 'In days']
repetition integer [default: 0]
due_at timestamp
last_reviewed_at timestamp [note: 'nullable']
created_at timestamp
updated_at timestamp

indexes {
(user_id, card_id) [unique]
}
}

// ===================================
// Media & Assets
// ===================================

Table media {
id integer [pk, increment]
owner_user_id integer [ref: > users.id, note: 'nullable']
storage_key varchar
original_name varchar [note: 'nullable']
mime_type varchar [note: 'nullable']
source_media_id varchar [note: 'For external mapping like Anki IDs']
created_at timestamp
updated_at timestamp
}

Table note_media {
id integer [pk, increment]
note_id integer [ref: > notes.id]
media_id integer [ref: > media.id]
field_name varchar [note: 'Which field uses this media?']

indexes {
(note_id, media_id, field_name) [unique]
}
}

// ===================================
// Organization: Tags
// ===================================

Table tags {
id integer [pk, increment]
name varchar [unique]
}

Table note_tags {
note_id integer [ref: > notes.id]
tag_id integer [ref: > tags.id]

indexes {
(note_id, tag_id) [pk]
}
}

// ===================================
// Laravel Default Authentication
// ===================================

Table users {
id integer [pk, increment]
name varchar
email varchar [unique]
email_verified_at timestamp
password varchar
remember_token varchar
created_at timestamp
updated_at timestamp
}

Table sessions {
id varchar [pk]
user_id integer [ref: > users.id]
ip_address varchar
user_agent text
payload longtext
last_activity integer
}

Table password_reset_tokens {
email varchar [pk]
token varchar
created_at timestamp
}

// ===================================
// Laravel Jobs & Caching
// ===================================

Table jobs {
id integer [pk, increment]
queue varchar
payload longtext
attempts integer
reserved_at integer
available_at integer
created_at integer
}

Table job_batches {
id varchar [pk]
name varchar
total_jobs integer
pending_jobs integer
failed_jobs integer
failed_job_ids longtext
options mediumtext
cancelled_at integer
created_at integer
finished_at integer
}

Table failed_jobs {
id integer [pk, increment]
uuid varchar [unique]
connection text
queue text
payload longtext
exception longtext
failed_at timestamp
}

Table cache {
key varchar [pk]
value mediumtext
expiration integer
}

Table cache_locks {
key varchar [pk]
owner varchar
expiration integer
}
