<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-clients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import clients from data.csv file in the project root';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $csvFile = base_path('data.csv'); // data.csv is now in the backend root

        if (!file_exists($csvFile)) {
            $this->error("File not found: $csvFile");
            return 1;
        }

        $this->info("Reading file: $csvFile");

        $file = fopen($csvFile, 'r');
        $header = fgetcsv($file);
        
        // Normalize headers to lower case and trim spaces
        $header = array_map(function($h) {
            return trim(strtolower($h));
        }, $header);

        $this->info("Headers found: " . implode(', ', $header));

        $clientNameIndex = array_search('client name', $header);
        $panNumberIndex = array_search('pan number', $header);
        $contactNumberIndex = array_search('contact number', $header);

        if ($clientNameIndex === false) {
            $this->error("Column 'CLIENT NAME' not found.");
            return 1;
        }

        $count = 0;
        $skipped = 0;
        $updated = 0;
        $created = 0;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($file)) !== false) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Pad row if it's shorter than header
                if (count($row) < count($header)) {
                    $row = array_pad($row, count($header), null);
                }

                $name = trim($row[$clientNameIndex] ?? '');
                $pan = isset($panNumberIndex) && $panNumberIndex !== false ? trim($row[$panNumberIndex] ?? '') : null;
                $phone = isset($contactNumberIndex) && $contactNumberIndex !== false ? trim($row[$contactNumberIndex] ?? '') : null;

                if (empty($name)) {
                    $skipped++;
                    continue;
                }

                // Clean up PAN (remove spaces, uppercase)
                if (!empty($pan)) {
                    $pan = strtoupper(str_replace(' ', '', $pan));
                    // Basic validation: PAN should be 10 chars usually, but we won't be too strict here unless requested
                } else {
                    $pan = null;
                }

                // Clean up Phone
                if (!empty($phone)) {
                    // Remove non-numeric characters except maybe +
                    // $phone = preg_replace('/[^0-9+]/', '', $phone);
                } else {
                    $phone = null;
                }

                $this->line("Processing: $name | PAN: $pan | Phone: $phone");

                // Logic to find existing client
                $client = null;

                if ($pan) {
                    $client = Client::where('pan_number', $pan)->first();
                }

                if (!$client) {
                    // Fallback to name check if PAN is missing or not found
                    // Be careful with name matching, maybe exact match for now
                    $client = Client::where('business_name', $name)->first();
                }

                if ($client) {
                    $client->update([
                        'business_name' => $name,
                        'pan_number' => $pan ?: $client->pan_number, // Don't overwrite with null if we have one? Or should we? Let's keep existing if new is null
                        'phone' => $phone ?: $client->phone,
                    ]);
                    $updated++;
                } else {
                    Client::create([
                        'business_name' => $name,
                        'pan_number' => $pan,
                        'phone' => $phone,
                        // Email is nullable now, so we don't need to provide it
                    ]);
                    $created++;
                }
                $count++;
            }

            DB::commit();
            $this->info("Import completed.");
            $this->info("Total processed: $count");
            $this->info("Created: $created");
            $this->info("Updated: $updated");
            $this->info("Skipped (empty name): $skipped");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error occurred: " . $e->getMessage());
            return 1;
        } finally {
            fclose($file);
        }

        return 0;
    }
}
