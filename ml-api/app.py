from fastapi import FastAPI, HTTPException, Query
from fastapi.middleware.cors import CORSMiddleware
from contextlib import asynccontextmanager
import joblib
import numpy as np
import pandas as pd
import os

@asynccontextmanager
async def lifespan(app: FastAPI):
    global cb_model, cf_model, books_df
    model_dir = os.path.join(os.path.dirname(__file__), "model")
    try:
        cb_model  = joblib.load(os.path.join(model_dir, "content_based.pkl"))
        cf_model  = joblib.load(os.path.join(model_dir, "collaborative.pkl"))
        books_df  = pd.read_pickle(os.path.join(model_dir, "books.pkl"))
        print("✅ Models loaded successfully")
    except FileNotFoundError:
        print("⚠️  Model files not found. Run train.py first.")
        cb_model = cf_model = books_df = None
    yield

app = FastAPI(title="BookWise Recommendation API", lifespan=lifespan)
app.add_middleware(CORSMiddleware, allow_origins=["*"], allow_methods=["*"], allow_headers=["*"])

cb_model = cf_model = books_df = None
BOOK_COLS = ["book_id", "isbn", "title", "authors", "image_url", "average_rating"]


def _format(df: pd.DataFrame) -> list[dict]:
    cols = [c for c in BOOK_COLS if c in df.columns]
    return df[cols].to_dict("records")


@app.get("/")
def root():
    return {"status": "ok", "message": "BookWise ML API is running"}


@app.get("/recommend/content")
def recommend_content(isbn: str = Query(None), book_id: int = Query(None), top_k: int = Query(10)):
    if cb_model is None or books_df is None:
        raise HTTPException(503, "Model belum di-load.")

    idx = None
    if book_id is not None:
        idx = cb_model["book_id_index"].get(int(book_id))
    elif isbn is not None:
        idx = cb_model["isbn_index"].get(str(isbn))

    if idx is None:
        raise HTTPException(404, "Buku tidak ditemukan.")

    sim_scores   = sorted(enumerate(cb_model["cosine_sim"][idx]), key=lambda x: x[1], reverse=True)[1:top_k+1]
    book_indices = [i[0] for i in sim_scores]
    return {"method": "content-based", "recommendations": _format(books_df.iloc[book_indices])}


@app.get("/recommend/collaborative")
def recommend_collaborative(user_id: str = Query(...), top_k: int = Query(10)):
    if cf_model is None or books_df is None:
        raise HTTPException(503, "Model belum di-load.")

    uid = int(user_id)
    user2idx = cf_model["user2idx"]

    if uid not in user2idx:
        # User baru: fallback ke buku dengan average_rating tertinggi
        top = books_df.nlargest(top_k, "average_rating")
        return {"method": "collaborative", "recommendations": _format(top)}

    user_idx         = user2idx[uid]
    item_user_matrix = cf_model["item_user_matrix"]
    model            = cf_model["model"]
    idx2book         = cf_model["idx2book"]

    # user_items = baris user dari matrix (transposed)
    user_items = item_user_matrix.T.tocsr()
    ids, scores = model.recommend(user_idx, user_items[user_idx], N=top_k, filter_already_liked_items=True)

    top_book_ids = [idx2book[i] for i in ids]
    result = books_df[books_df["book_id"].isin(top_book_ids)]
    return {"method": "collaborative", "recommendations": _format(result)}


@app.get("/recommend/hybrid")
def recommend_hybrid(user_id: str = Query(...), isbn: str = Query(None), book_id: int = Query(None), top_k: int = Query(10)):
    cb = recommend_content(isbn=isbn, book_id=book_id, top_k=top_k * 2)["recommendations"]
    cf = recommend_collaborative(user_id=user_id, top_k=top_k * 2)["recommendations"]

    cf_ids  = {b["book_id"] for b in cf}
    hybrid  = [b for b in cb if b["book_id"] in cf_ids]
    hybrid += [b for b in cb if b["book_id"] not in cf_ids]
    return {"method": "hybrid", "recommendations": hybrid[:top_k]}


@app.get("/books/search")
def search_books(q: str = Query(...), limit: int = Query(20)):
    if books_df is None:
        raise HTTPException(503, "Model belum di-load.")
    mask   = (books_df["title"].str.contains(q, case=False, na=False) |
              books_df["authors"].str.contains(q, case=False, na=False))
    return {"results": _format(books_df[mask].head(limit))}
