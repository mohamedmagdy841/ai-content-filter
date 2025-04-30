from aio_pika import connect_robust, Message

RABBITMQ_URL = "amqp://guest:guest@localhost:5672/"  # Or use Docker network URL if needed

async def get_connection():
    return await connect_robust(RABBITMQ_URL)

async def send_to_queue(queue_name: str, body: str):
    connection = await get_connection()
    async with connection:
        channel = await connection.channel()
        queue = await channel.declare_queue(queue_name, durable=True)
        await channel.default_exchange.publish(
            Message(body.encode()),
            routing_key=queue.name,
        )
