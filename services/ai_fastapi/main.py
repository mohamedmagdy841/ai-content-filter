from fastapi import FastAPI
from routers import analyze
from dotenv import load_dotenv
from services.rabbitmq import send_to_queue
load_dotenv()

app = FastAPI()

app.include_router(analyze.router)

@app.post("/test-rabbit")
async def send_message():
    await send_to_queue("default", "Hello from FastAPI!")
    return {"message": "Sent to RabbitMQ"}
