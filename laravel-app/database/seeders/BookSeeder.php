<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

/**
 * Import buku dari GoodBooks-10k books.csv ke database.
 * Jalankan: php artisan db:seed --class=BookSeeder
 *
 * Download dataset: https://www.kaggle.com/datasets/zygmuntz/goodbooks-10k
 * Letakkan books.csv di: ../data/books.csv
 */
class BookSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = base_path('../data/books.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("File tidak ditemukan: {$csvPath}");
            $this->command->info("Download dari: https://www.kaggle.com/datasets/zygmuntz/goodbooks-10k");
            return;
        }

        $handle = fopen($csvPath, 'r');
        // Header: book_id,goodreads_book_id,best_book_id,work_id,books_count,isbn,isbn13,
        //         authors,original_publication_year,original_title,title,language_code,
        //         average_rating,ratings_count,work_ratings_count,...,image_url,small_image_url
        $header = fgetcsv($handle);
        $colMap = array_flip($header);

        $batch = [];
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $isbn = trim($row[$colMap['isbn']] ?? '');
            // Fallback ke isbn13 atau book_id jika isbn kosong
            if ($isbn === '') {
                $isbn = trim($row[$colMap['isbn13']] ?? '');
            }
            if ($isbn === '') {
                $isbn = (string) trim($row[$colMap['book_id']] ?? '');
            }

            $year = trim($row[$colMap['original_publication_year']] ?? '');

            $batch[] = [
                'isbn'                => $isbn,
                'title'               => trim($row[$colMap['title']] ?? ''),
                'author'              => trim($row[$colMap['authors']] ?? ''),
                'year_of_publication' => is_numeric($year) ? (int) $year : null,
                'publisher'           => null, // GoodBooks-10k tidak punya kolom publisher
                'image_url'           => trim($row[$colMap['image_url']] ?? ''),
                'created_at'          => now(),
                'updated_at'          => now(),
            ];

            if (count($batch) >= 500) {
                Book::upsert($batch, ['isbn'], ['title', 'author', 'year_of_publication', 'image_url']);
                $count += count($batch);
                $batch  = [];
                $this->command->info("Imported {$count} books...");
            }
        }

        if ($batch) {
            Book::upsert($batch, ['isbn'], ['title', 'author', 'year_of_publication', 'image_url']);
            $count += count($batch);
        }

        fclose($handle);
        $this->command->info("✅ Total {$count} buku berhasil diimport.");
    }
}
