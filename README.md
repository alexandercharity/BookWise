# BookWise — Sistem Rekomendasi Buku

Sistem rekomendasi buku berbasis Machine Learning menggunakan dataset GoodBooks-10k.

## Struktur Project

```
BookWise/
├── laravel-app/     # Website (Laravel 11 + Blade + Tailwind)
├── ml-api/          # ML Recommendation Engine (FastAPI + Python)
│   ├── app.py       # FastAPI server
│   ├── train.py     # Script training model
│   ├── evaluate.py  # Script evaluasi RMSE, MAE, Precision, Recall
│   ├── model/       # Saved models (.pkl) — diisi setelah training
│   └── notebooks/   # Jupyter notebooks untuk laporan
└── data/            # Dataset GoodBooks-10k (download manual)
```

## Setup

### 1. Download Dataset
Download dari Kaggle: https://www.kaggle.com/datasets/zygmuntz/goodbooks-10k

Letakkan file berikut di folder `data/`:
- `books.csv`
- `ratings.csv`

### 2. Setup ML API (Python)
```bash
# Install dependencies
python -m pip install -r ml-api/requirements.txt

# Training model (jalankan sekali, ~1-2 menit)
python ml-api/train.py

# Jalankan API server
uvicorn ml-api.app:app --reload --port 8000

# (Opsional) Evaluasi model
python ml-api/evaluate.py
```

### 3. Setup Laravel
```bash
cd laravel-app
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm install && npm run build
php artisan serve --port=8001
```

### 4. Akses
- Website: http://127.0.0.1:8001
- ML API: http://127.0.0.1:8000
- ML API Docs: http://127.0.0.1:8000/docs

## Metode yang Digunakan
- Content-Based Filtering (TF-IDF + Cosine Similarity)
- Collaborative Filtering (ALS via implicit library)
- Hybrid (gabungan keduanya)

## Hasil Evaluasi
| Metode | RMSE | MAE | Precision@10 | Recall@10 |
|---|---|---|---|---|
| Content-Based | — | — | 0.1255 | 0.0731 |
| Collaborative (ALS) | 2.9987 | 2.8294 | — | — |
| Hybrid | 2.9987 | 2.8294 | 0.1255 | 0.0731 |
