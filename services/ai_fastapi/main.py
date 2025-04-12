from fastapi import FastAPI
from routers import analyze
from dotenv import load_dotenv

load_dotenv()

app = FastAPI()

app.include_router(analyze.router)

