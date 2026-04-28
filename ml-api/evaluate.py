"""
evaluate.py — Evaluasi RMSE, MAE, Precision@K, Recall@K untuk laporan
Jalankan: python ml-api/evaluate.py
"""

import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from surprise import SVD, Dataset, Reader, accuracy
import joblib
import os

DATA_DIR  = os.path.join(os.path.dirname(__file__), "../data")
MODEL_DIR = os.path.join(os.path.dirname(__file__), "model")

print("Loading data & models...")
books_df = pd.read_pickle(f"{MODEL_DIR}/books.pkl")
cb_model = joblib.load(f"{MODEL_DIR}/content_based.pkl")
cf_model = joblib.load(f"{MODEL_DIR}/collaborative.pkl")

ratings = pd.read_csv(f"{DATA_DIR}/ratings.csv")
ratings = ratings[ratings["rating"].between(1, 5)]

# ── 1. Collaborative Filtering (SVD) — RMSE & MAE ─────────────────────────
print("\n── Collaborative Filtering (SVD) ──")
reader = Reader(rating_scale=(1, 5))
data   = Dataset.load_from_df(ratings[["user_id", "book_id", "rating"]], reader)

trainset, testset = data.build_full_trainset(), None
# Pakai 20% sebagai test
from surprise.model_selection import train_test_split as surprise_split
trainset, testset = surprise_split(data, test_size=0.2, random_state=42)

cf_model.fit(trainset)
predictions = cf_model.test(testset)

cf_rmse = accuracy.rmse(predictions, verbose=False)
cf_mae  = accuracy.mae(predictions, verbose=False)
print(f"  RMSE : {cf_rmse:.4f}")
print(f"  MAE  : {cf_mae:.4f}")
print(f"  (n test samples: {len(predictions):,})")

# ── 2. Content-Based — Precision@K & Recall@K ─────────────────────────────
print("\n── Content-Based Filtering ──")
isbn_index    = cb_model["isbn_index"]
cosine_sim    = cb_model["cosine_sim"]
book_id_index = cb_model["book_id_index"]

def cb_recommend(book_id, k=10):
    idx = book_id_index.get(int(book_id))
    if idx is None:
        return []
    scores = sorted(enumerate(cosine_sim[idx]), key=lambda x: x[1], reverse=True)[1:k+1]
    return books_df.iloc[[i[0] for i in scores]]["book_id"].tolist()

high = ratings[ratings["rating"] >= 4]
sample_users = high["user_id"].value_counts().head(300).index

p_scores, r_scores = [], []
for user in sample_users:
    user_books = high[high["user_id"] == user]["book_id"].tolist()
    if len(user_books) < 2:
        continue
    recs = cb_recommend(user_books[0], 10)
    if not recs:
        continue
    relevant = set(user_books[1:])
    hit = len(set(recs) & relevant)
    p_scores.append(hit / 10)
    r_scores.append(hit / len(relevant) if relevant else 0)

cb_precision = np.mean(p_scores)
cb_recall    = np.mean(r_scores)
print(f"  Precision@10 : {cb_precision:.4f}")
print(f"  Recall@10    : {cb_recall:.4f}")
print(f"  (n users evaluated: {len(p_scores)})")

# ── 3. Ringkasan tabel ─────────────────────────────────────────────────────
print("\n" + "="*55)
print(f"{'Metode':<25} {'RMSE':>8} {'MAE':>8} {'P@10':>8} {'R@10':>8}")
print("-"*55)
print(f"{'Content-Based':<25} {'—':>8} {'—':>8} {cb_precision:>8.4f} {cb_recall:>8.4f}")
print(f"{'Collaborative (SVD)':<25} {cf_rmse:>8.4f} {cf_mae:>8.4f} {'—':>8} {'—':>8}")
print(f"{'Hybrid':<25} {cf_rmse:>8.4f} {cf_mae:>8.4f} {cb_precision:>8.4f} {cb_recall:>8.4f}")
print("="*55)
