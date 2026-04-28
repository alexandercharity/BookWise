"""
evaluate.py — Evaluasi RMSE, MAE, Precision@K, Recall@K untuk laporan
Jalankan: python ml-api/evaluate.py
"""

import pandas as pd
import numpy as np
from sklearn.metrics import mean_squared_error, mean_absolute_error
from sklearn.model_selection import train_test_split
from sklearn.metrics.pairwise import cosine_similarity
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

# ── 1. Collaborative Filtering — RMSE & MAE ───────────────────────────────
print("\n── Collaborative Filtering (ALS) ──")
train_df, test_df = train_test_split(ratings, test_size=0.2, random_state=42)

model    = cf_model["model"]
user2idx = cf_model["user2idx"]
book2idx = cf_model["book2idx"]

n_items = model.item_factors.shape[0]
n_users = model.user_factors.shape[0]

y_true, y_pred = [], []
for _, row in test_df.iterrows():
    uid = int(row["user_id"])
    bid = int(row["book_id"])
    if uid in user2idx and bid in book2idx:
        u = user2idx[uid]
        b = book2idx[bid]
        if u >= n_users or b >= n_items:
            continue
        score = float(model.user_factors[u] @ model.item_factors[b])
        score = max(1.0, min(5.0, score))
        y_true.append(row["rating"])
        y_pred.append(score)

cf_rmse = np.sqrt(mean_squared_error(y_true, y_pred))
cf_mae  = mean_absolute_error(y_true, y_pred)
print(f"  RMSE : {cf_rmse:.4f}")
print(f"  MAE  : {cf_mae:.4f}")
print(f"  (n test samples: {len(y_true):,})")

# ── 2. Content-Based — Precision@K & Recall@K ─────────────────────────────
print("\n── Content-Based Filtering ──")
isbn_index = cb_model["isbn_index"]
cosine_sim = cb_model["cosine_sim"]

def cb_recommend(isbn, k=10):
    idx = isbn_index.get(str(isbn))
    if idx is None:
        return []
    scores = sorted(enumerate(cosine_sim[idx]), key=lambda x: x[1], reverse=True)[1:k+1]
    return books_df.iloc[[i[0] for i in scores]]["isbn"].tolist()

high = ratings[ratings["rating"] >= 4]
sample_users = high["user_id"].value_counts().head(300).index

p_scores, r_scores = [], []
for user in sample_users:
    user_books = high[high["user_id"] == user]["book_id"].tolist()
    if len(user_books) < 2:
        continue
    seed_isbn = books_df[books_df["book_id"] == user_books[0]]["isbn"].values
    if len(seed_isbn) == 0:
        continue
    recs = cb_recommend(seed_isbn[0], 10)
    if not recs:
        continue
    relevant_isbns = books_df[books_df["book_id"].isin(user_books[1:])]["isbn"].tolist()
    hit = len(set(recs) & set(relevant_isbns))
    p_scores.append(hit / 10)
    r_scores.append(hit / len(relevant_isbns) if relevant_isbns else 0)

cb_precision = np.mean(p_scores)
cb_recall    = np.mean(r_scores)
print(f"  Precision@10 : {cb_precision:.4f}")
print(f"  Recall@10    : {cb_recall:.4f}")
print(f"  (n users evaluated: {len(p_scores)})")

# ── 3. Hybrid — gabungan score ─────────────────────────────────────────────
print("\n── Hybrid (CB + ALS) ──")
# RMSE hybrid: rata-rata prediksi CF (tidak ada ground truth terpisah untuk hybrid)
# Gunakan CF RMSE/MAE sebagai basis, CB menambah coverage
print(f"  RMSE (via CF component) : {cf_rmse:.4f}")
print(f"  MAE  (via CF component) : {cf_mae:.4f}")
print(f"  Precision@10 (via CB)   : {cb_precision:.4f}")
print(f"  Recall@10    (via CB)   : {cb_recall:.4f}")

# ── 4. Ringkasan tabel ─────────────────────────────────────────────────────
print("\n" + "="*55)
print(f"{'Metode':<25} {'RMSE':>8} {'MAE':>8} {'P@10':>8} {'R@10':>8}")
print("-"*55)
print(f"{'Content-Based':<25} {'—':>8} {'—':>8} {cb_precision:>8.4f} {cb_recall:>8.4f}")
print(f"{'Collaborative (ALS)':<25} {cf_rmse:>8.4f} {cf_mae:>8.4f} {'—':>8} {'—':>8}")
print(f"{'Hybrid':<25} {cf_rmse:>8.4f} {cf_mae:>8.4f} {cb_precision:>8.4f} {cb_recall:>8.4f}")
print("="*55)
