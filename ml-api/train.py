"""
train.py — Melatih model Content-Based dan Collaborative Filtering (SVD)
Jalankan sekali: python ml-api/train.py
Dataset: GoodBooks-10k (letakkan di ./data/)
  - books.csv
  - ratings.csv
Download: https://www.kaggle.com/datasets/zygmuntz/goodbooks-10k
"""

import pandas as pd
import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
from surprise import SVD, Dataset, Reader
from surprise.model_selection import GridSearchCV
import joblib
import os

DATA_DIR  = os.path.join(os.path.dirname(__file__), "../data")
MODEL_DIR = os.path.join(os.path.dirname(__file__), "model")
os.makedirs(MODEL_DIR, exist_ok=True)

# ── 1. Load & Preprocess ───────────────────────────────────────────────────
print("Loading data...")
books   = pd.read_csv(f"{DATA_DIR}/books.csv")
ratings = pd.read_csv(f"{DATA_DIR}/ratings.csv")

print(f"Raw books: {len(books):,} | Raw ratings: {len(ratings):,}")

books = books[["book_id", "isbn", "authors", "title",
               "original_publication_year", "image_url", "average_rating"]].copy()
books.dropna(subset=["book_id", "title"], inplace=True)
books.drop_duplicates(subset="book_id", inplace=True)
books["isbn"]    = books["isbn"].fillna(books["book_id"].astype(str))
books["book_id"] = books["book_id"].astype(int)

ratings = ratings[["user_id", "book_id", "rating"]].dropna()
ratings = ratings[ratings["rating"].between(1, 5)]

user_counts = ratings["user_id"].value_counts()
book_counts = ratings["book_id"].value_counts()
ratings = ratings[ratings["user_id"].isin(user_counts[user_counts >= 3].index)]
ratings = ratings[ratings["book_id"].isin(book_counts[book_counts >= 5].index)]

print(f"Filtered books: {len(books):,} | Filtered ratings: {len(ratings):,}")

# ── 2. Content-Based Filtering ─────────────────────────────────────────────
print("\nTraining Content-Based model...")
books["features"] = books["title"].fillna("") + " " + books["authors"].fillna("")

tfidf      = TfidfVectorizer(max_features=5000, stop_words="english")
tfidf_mat  = tfidf.fit_transform(books["features"])
cosine_sim = cosine_similarity(tfidf_mat, tfidf_mat)

book_id_index = {int(bid): idx for idx, bid in enumerate(books["book_id"])}
isbn_index    = {str(isbn): idx for idx, isbn in enumerate(books["isbn"])}

cb_model = {
    "cosine_sim":    cosine_sim,
    "book_id_index": book_id_index,
    "isbn_index":    isbn_index,
}
joblib.dump(cb_model, f"{MODEL_DIR}/content_based.pkl")
print("✅ Content-Based model saved.")

# ── 3. Collaborative Filtering (SVD) ──────────────────────────────────────
print("\nTraining Collaborative Filtering (SVD)...")
reader = Reader(rating_scale=(1, 5))
data   = Dataset.load_from_df(ratings[["user_id", "book_id", "rating"]], reader)

param_grid = {
    "n_epochs": [20, 30],
    "lr_all":   [0.005, 0.01],
    "reg_all":  [0.02, 0.1],
}
print("Running GridSearchCV (beberapa menit)...")
gs = GridSearchCV(SVD, param_grid, measures=["rmse"], cv=3, n_jobs=-1)
gs.fit(data)

best_params = gs.best_params["rmse"]
print(f"Best params: {best_params}")
print(f"Best RMSE (CV): {gs.best_score['rmse']:.4f}")

trainset = data.build_full_trainset()
cf_model = SVD(**best_params)
cf_model.fit(trainset)
joblib.dump(cf_model, f"{MODEL_DIR}/collaborative.pkl")
print("✅ Collaborative (SVD) model saved.")

# ── 4. Simpan books dataframe ──────────────────────────────────────────────
books.to_pickle(f"{MODEL_DIR}/books.pkl")
print("✅ Books dataframe saved.")
print("\nTraining selesai. Jalankan: uvicorn ml-api.app:app --reload")
