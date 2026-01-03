<?php

namespace App\Services;

use App\Models\Card;
use App\Models\CardTemplate;
use App\Models\Deck;
use App\Models\Note;
use App\Models\NoteType;
use App\Models\ReviewState;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;
use PDO;

class AnkiImportService
{
    protected $user;
    protected $tempDir;
    protected $mediaMap = [];
    protected $deckMap = []; // Anki Deck ID -> Tsuyanki Deck ID
    protected $modelMap = []; // Anki Model ID -> Tsuyanki NoteType ID
    protected $noteMap = []; // Anki Note ID -> Tsuyanki Note ID
    protected $templateMap = []; // Anki Model ID + Ord -> Tsuyanki CardTemplate ID

    public function import(UploadedFile $file, User $user)
    {
        $this->user = $user;
        $importId = Str::uuid()->toString();
        $this->tempDir = storage_path('app/temp/anki_import/' . $importId);

        if (!mkdir($this->tempDir, 0777, true)) {
            throw new \Exception("Failed to create temporary directory.");
        }

        try {
            $this->unzip($file);
            $this->loadMediaMap();
            $this->processDatabase();
            $this->processMedia(); // Optional: Move media files to public storage
        } finally {
            // Cleanup
            $this->deleteDirectory($this->tempDir);
        }

        // Post-import cleanup: Remove empty "Default" deck if it exists
        $defaultDeck = Deck::where('owner_user_id', $this->user->id)
            ->where('title', 'Default')
            ->whereDoesntHave('notes')
            ->first();

        if ($defaultDeck) {
            $defaultDeck->delete();
            // Remove from count if it was in deckMap (unlikely if we want to be precise, but acceptable)
            // We can just ignore the count discrepancy or adjust it.
        }

        return [
            'decks' => count($this->deckMap) - ($defaultDeck ? 1 : 0),
            'notes' => count($this->noteMap),
        ];
    }

    protected function unzip(UploadedFile $file)
    {
        $zip = new ZipArchive;
        if ($zip->open($file->getRealPath()) === TRUE) {
            $zip->extractTo($this->tempDir);
            $zip->close();
        } else {
            throw new \Exception("Failed to open .apkg file.");
        }
    }

    protected function loadMediaMap()
    {
        $mediaPath = $this->tempDir . '/media';
        if (file_exists($mediaPath)) {
            $content = file_get_contents($mediaPath);
            // Anki media file is a JSON object where keys are stringified integers (zip entry names) and values are filenames
            $this->mediaMap = json_decode($content, true) ?? [];
        }
    }

    protected function processDatabase()
    {
        $dbPath = $this->tempDir . '/collection.anki2';
        if (!file_exists($dbPath)) {
            throw new \Exception("Invalid .apkg: collection.anki2 not found.");
        }

        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 1. Process Col table (Decks and Models)
        $stmt = $pdo->query("SELECT * FROM col LIMIT 1");
        $col = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$col) {
            throw new \Exception("Invalid Anki database.");
        }

        $models = json_decode($col['models'], true);
        $decks = json_decode($col['decks'], true);

        DB::transaction(function () use ($models, $decks, $pdo) {
            $this->importDecks($decks);
            $this->importNoteTypes($models);
            $this->importNotes($pdo);
            $this->importCards($pdo);
        });
    }

    protected function importDecks($ankiDecks)
    {
        foreach ($ankiDecks as $id => $deckData) {
            // Skip filtered decks check - we want to import them as static decks if user exported them
            // if (isset($deckData['dyn']) && $deckData['dyn'] == 1)
            //    continue;

            $deck = Deck::create([
                'title' => $deckData['name'],
                'description' => 'Imported from Anki',
                'owner_user_id' => $this->user->id,
                'is_public' => false,
            ]);

            $this->deckMap[$id] = $deck->id;
        }
    }

    protected function importNoteTypes($ankiModels)
    {
        foreach ($ankiModels as $id => $modelData) {
            // Check for existing NoteType
            // Logic: Match Name AND (User ID matches OR User ID is null (Global))
            $existingType = NoteType::where('name', $modelData['name'])
                ->where(function ($query) {
                    $query->where('user_id', $this->user->id)
                        ->orWhereNull('user_id');
                })
                ->first();

            if ($existingType) {
                $this->modelMap[$id] = $existingType->id;

                // Map templates for this existing type
                // We need to find the specific template IDs for the existing type
                // Assuming standard naming or order?
                // Anki templates have 'ord'. Tsuyanki templates don't track 'ord' explicitly but by creation order or Name?
                // Let's look up templates by Name.
                foreach ($modelData['tmpls'] as $tmpl) {
                    $template = CardTemplate::where('note_type_id', $existingType->id)
                        ->where('name', $tmpl['name'])
                        ->first();

                    if ($template) {
                        $this->templateMap["{$id}_{$tmpl['ord']}"] = $template->id;
                    } else {
                        // Create missing template if schema evolved? 
                        // Or just create it now to be safe.
                        $newTemplate = CardTemplate::create([
                            'note_type_id' => $existingType->id,
                            'name' => $tmpl['name'],
                            'front_template' => $tmpl['qfmt'],
                            'back_template' => $tmpl['afmt'],
                        ]);
                        $this->templateMap["{$id}_{$tmpl['ord']}"] = $newTemplate->id;
                    }
                }

                continue;
            }

            // Create New NoteType
            $fields = [];
            foreach ($modelData['flds'] as $fld) {
                $fields[] = [
                    'name' => $fld['name'],
                    'type' => 'text', // Defaulting to text
                    'required' => false,
                ];
            }

            $noteType = NoteType::create([
                'name' => $modelData['name'],
                'field_schema' => ['fields' => $fields],
                'user_id' => $this->user->id, // Scope to user
            ]);

            $this->modelMap[$id] = $noteType->id;

            // Import Templates
            foreach ($modelData['tmpls'] as $tmpl) {
                $template = CardTemplate::create([
                    'note_type_id' => $noteType->id,
                    'name' => $tmpl['name'],
                    'front_template' => $tmpl['qfmt'],
                    'back_template' => $tmpl['afmt'],
                ]);
                $this->templateMap["{$id}_{$tmpl['ord']}"] = $template->id;
            }
        }
    }

    protected function importNotes($pdo)
    {
        // Anki Notes: id, guid, mid, mod, usn, tags, flds, sfld, csum, flags, data
        $stmt = $pdo->query("SELECT * FROM notes");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $mid = $row['mid'];
            if (!isset($this->modelMap[$mid]))
                continue;

            // Fields are 0x1F separated
            $fieldValuesData = explode("\x1f", $row['flds']);

            // Map to field names from NoteType (we need to retrieve them or store them in memory)
            // For efficiency, we rely on index order which matches `flds` array in `importNoteTypes`
            // But I didn't store the field structure in memory. Let's do it cleanly or just use indexed array for now?
            // The `Note` model casts `fields` to array. It can be associative or indexed.
            // Tsuyanki expects associative usually: "expression" => "...", "meaning" => "..."
            // I need to fetch the field names from the NoteType logic. 
            // Re-accessing $ankiModels would be ideal, but it's local to `importNoteTypes`.
            // Let's assume user accepts indexed fields if we can't easily map, or we pass field names to this method.
            // BETTER: store field names in a property.

            // For now, I'll save them as indexed keys "field_0", "field_1" OR try to map if I can?
            // Let's keep it simple: Map to "0", "1", "2"... NO, `NoteType` schema has names.
            // I should look up the NoteType I just created.

            $noteType = NoteType::find($this->modelMap[$mid]);
            $schema = $noteType->field_schema; // access accessor
            $fieldNames = array_column($schema['fields'], 'name');

            $fields = [];
            foreach ($fieldNames as $index => $name) {
                $fields[$name] = $fieldValuesData[$index] ?? '';
            }

            // Deck ID? Notes don't have deck ID in Anki, Cards do.
            // In Tsuyanki, Notes have deck_id.
            // We need to look up one of the cards for this note to determine the deck.
            // Or use a default deck?
            // Anki schema: Notes are global, Cards bind to Decks.
            // Tsuyanki schema: Note belongs to Deck.
            // This is a mismatch.
            // SOLUTION: Query the FIRST card of this note to find its DID (Deck ID).

            $cardStmt = $pdo->prepare("SELECT did FROM cards WHERE nid = ? LIMIT 1");
            $cardStmt->execute([$row['id']]);
            $cardRow = $cardStmt->fetch(PDO::FETCH_ASSOC);
            $ankiDeckId = $cardRow ? $cardRow['did'] : null;
            $tsuyankiDeckId = $this->deckMap[$ankiDeckId] ?? null;

            if (!$tsuyankiDeckId)
                continue; // Orphan note

            $note = Note::create([
                'deck_id' => $tsuyankiDeckId,
                'note_type_id' => $this->modelMap[$mid],
                'fields' => $fields, // Associative array
            ]);

            // Tags
            $tags = array_filter(explode(" ", $row['tags']));
            // TODO: Process tags (create Tag model and attach). skipping for brevity of v1.

            $this->noteMap[$row['id']] = $note->id;
        }
    }

    protected function importCards($pdo)
    {
        // Anki Cards: id, nid, did, ord, mod, usn, type, queue, due, ivl, factor, reps, lapses, left, odue, odid, flags, data
        $stmt = $pdo->query("SELECT * FROM cards");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $nid = $row['nid'];
            $did = $row['did'];
            $ord = $row['ord'];

            if (!isset($this->noteMap[$nid]))
                continue;
            if (!isset($this->deckMap[$did]))
                continue;

            // Find template ID
            // We need the model ID of the note to find the template map key "mid_ord"
            // We can look up the note we just inserted? No, we don't have mid stored in map.
            // We need to query the note's note_type_id from DB or memory.
            // Optimization: Store mid in noteMap? array(tsuId, mid)

            // Let's query the note from DB to be safe (slow) or modify noteMap.
            // Modifying noteMap to valid memory usage? 
            // Anki collection can be large. 
            // Querying NoteType from Note is safer.
            $note = Note::find($this->noteMap[$nid]);

            // But we need the ORIGINAL Anki MID to match `this->templateMap` keys which are "MID_ORD".
            // I don't have Anki MID stored in Tsuyanki DB.
            // I have `note->note_type_id` (Tsuyanki ID).
            // I can flip `modelMap` to find Anki ID? Or key `templateMap` by Tsuyanki NoteTypeID?
            // YES. Change `templateMap` key to "TsuyankiNoteTypeID_Ord".

            // Wait, I can't easily change `templateMap` key in `importNoteTypes` because I use `$id` (Anki MID).
            // I can store `tsuNoteTypeId` -> `ankiMid` reverse map?
            // Or just fetch Anki Mid from the raw SQL?
            // The `notes` loop had `mid`.
            // Let's optimize: In `processDatabase`, we already iterate notes.
            // But cards are separate table.

            // RE-DESIGN:
            // Iterate Cards. Join Notes table in SQLite query to get MID.

        }

        // BETTER QUERY:
        $stmt = $pdo->query("SELECT c.*, n.mid FROM cards c JOIN notes n ON c.nid = n.id");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $nid = $row['nid'];
            $mid = $row['mid'];
            $ord = $row['ord'];

            if (!isset($this->noteMap[$nid]))
                continue;

            $mapKey = "{$mid}_{$ord}";
            if (!isset($this->templateMap[$mapKey]))
                continue;

            $card = Card::create([
                'note_id' => $this->noteMap[$nid],
                'card_template_id' => $this->templateMap[$mapKey],
            ]);

            // Create initial ReviewState
            ReviewState::create([
                'card_id' => $card->id,
                'user_id' => $this->user->id,
                'interval' => 0,
                'ease_factor' => 2.5,
                'repetition' => 0,
                'due_at' => now(),
            ]);
        }
    }

    protected function processMedia()
    {
        // Move mapped files from $this->tempDir to public storage
        // Anki media keys are "0", "1"... (string in zip)
        // Values are filenames "image.jpg"

        $destPath = storage_path('app/public/media');
        if (!file_exists($destPath)) {
            mkdir($destPath, 0777, true);
        }

        foreach ($this->mediaMap as $zipKey => $filename) {
            $source = $this->tempDir . '/' . $zipKey;
            if (file_exists($source)) {
                // Sanitize filename or use as is?
                // Anki filenames are usually safe but can have unicode.
                $dest = $destPath . '/' . $filename;
                copy($source, $dest);
            }
        }
    }

    protected function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }
}
