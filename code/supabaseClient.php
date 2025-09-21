<?php
require __DIR__ . '/../vendor/autoload.php';

use Supabase\SupabaseClient;

$url = "https://kzjiizzttxpvawewpovr.supabase.co";   // Supabase project URL
$key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imt6amlpenp0dHhwdmF3ZXdwb3ZyIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1NzkyNDcxNSwiZXhwIjoyMDczNTAwNzE1fQ.qZFTl-QNIj4ClJhgAneY9202t6H-Cj-TsujYGUsGQMg";                  // Supabase service role key

$client = new SupabaseClient($url, $key);
?>
