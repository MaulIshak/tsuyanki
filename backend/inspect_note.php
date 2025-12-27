$n = App\Models\Note::get()->first(function($note) { return str_contains(json_encode($note->fields), '2_i.mp3'); });
if ($n) {
echo "FIELDS_FOUND: " . json_encode($n->fields) . "\n";
echo "FRONT_TEMPLATE_FOUND: " . $n->cards->first()->template->front_template . "\n";
} else {
echo "NOTE_NOT_FOUND\n";
}
exit