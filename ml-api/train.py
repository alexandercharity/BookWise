"""
train.py — Melatih model Content-Based dan Collaborative Filtering (ALS)
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
import implicit
from scipy.sparse import csr_matrix
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
books["isbn"] = books["isbn"].fillna(books["book_id"].astype(str))
books["book_id"] = books["book_id"].astype(int)

ratings = ratings[["user_id", "book_id", "rating"]].dropna()
ratings = ratings[ratings["rating"].between(1, 5)]

# Filter user & buku yang cukup aktif
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

# ── 3. Collaborative Filtering (ALS via implicit) ─────────────────────────
print("\nTraining Collaborative Filtering (ALS)...")

# Buat mapping user & book ke index integer
unique_users = ratings["user_id"].unique()
unique_books = ratings["book_id"].unique()
user2idx = {u: i for i, u in enumerate(unique_users)}
book2idx = {b: i for i, b in enumerate(unique_books)}
idx2user = {i: u for u, i in user2idx.items()}
idx2book = {i: b for b, i in book2idx.items()}

rows = ratings["book_id"].map(book2idx).values
cols = ratings["user_id"].map(user2idx).values
data = ratings["rating"].values.astype(np.float32)

# item-user matrix (implicit expects items x users)
item_user_matrix = csr_matrix((data, (rows, cols)),
                               shape=(len(unique_books), len(unique_users)))

model = implicit.als.AlternatingLeastSquares(factors=50, iterations=20, regularization=0.1)
model.fit(item_user_matrix)

cf_model = {
    "model":            model,
    "user2idx":         user2idx,
    "book2idx":         book2idx,
    "idx2book":         idx2book,
    "item_user_matrix": item_user_matrix,
    "unique_books":     unique_books,
}
joblib.dump(cf_model, f"{MODEL_DIR}/collaborative.pkl")
print("✅ Collaborative model saved.")

# ── 4. Simpan books dataframe ──────────────────────────────────────────────
books.to_pickle(f"{MODEL_DIR}/books.pkl")
print("✅ Books dataframe saved.")

print("\nTraining selesai. Jalankan: uvicorn ml-api/app:app --reload")
