# BookWise — Sistem Rekomendasi Buku

Proyek tugas kelompok mata kuliah Sistem Rekomendasi Bisnis.

## Struktur Project

```
BookWise/
├── laravel-app/     # Website (Laravel 11 + Blade + Tailwind)
├── ml-api/          # ML Recommendation Engine (FastAPI + Python)
│   ├── app.py       # FastAPI server
│   ├── train.py     # Script training model
│   ├── model/       # Saved models (.pkl) — diisi setelah training
│   └── notebooks/   # Jupyter notebooks untuk paper
└── data/            # Dataset Book-Crossing (download manual)
```

## Setup

### 1. Download Dataset
Download dari Kaggle: https://www.kaggle.com/datasets/ruchi798/bookcrossing-dataset
Letakkan file berikut di folder `data/`:
- `BX-Books.csv`
- `BX-Book-Ratings.csv`
- `BX-Users.csv`

### 2. Setup ML API (Python)
```bash
cd ml-api
pip install -r requirements.txt

# Training model (jalankan sekali, ~10-15 menit)
python train.py

# Jalankan API server
uvicorn app:app --reload --port 8000
```

### 3. Setup Laravel
```bash
cd laravel-app

# Copy .env dan isi DB credentials
cp .env.example .env
php artisan key:generate

# Setup database (MySQL)
# Buat database bernama 'bookwise' di MySQL
php artisan migrate

# Import buku dari dataset
php artisan db:seed --class=BookSeeder

# Jalankan server
php artisan serve
```

### 4. Akses Website
- Website: http://localhost:8000 (Laravel)
- ML API docs: http://localhost:8000/docs (FastAPI Swagger)

## Metode yang Digunakan
- Content-Based Filtering (TF-IDF + Cosine Similarity)
- Collaborative Filtering (SVD via Surprise library)
- Hybrid (gabungan keduanya)

## Evaluasi
- RMSE, MAE (rating prediction)
- Precision@K, Recall@K, NDCG@K (ranking)
