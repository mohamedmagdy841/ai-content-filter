import pika, json
from routers.analyze import analyze
from schemas.content import ContentData

connection = pika.BlockingConnection(pika.ConnectionParameters('127.0.0.1'))
channel = connection.channel()
channel.queue_declare(queue='post.analysis.request', durable=True)
channel.queue_declare(queue='post.analysis.response', durable=True)

def callback(ch, method, properties, body):
    print(" [x] Received message for analysis")
    raw_data = json.loads(body)
    post_id = raw_data.get("post_id")
    content_fields = {key: raw_data[key] for key in ["title", "content", "ai_model"]}
    data = ContentData(**content_fields)
    result = analyze(data)

    channel.basic_publish(
        exchange='',
        routing_key='post.analysis.response',
        body=json.dumps({
            "post_id": post_id,
            "status": "flagged" if result["is_flagged"] else "approved",
            "reason": result["reason"],
            "score": result["score"],
        })
    )
    
    print("Analysis sent back")

channel.basic_consume(queue='post.analysis.request', on_message_callback=callback, auto_ack=True)
print(' [*] Waiting for messages.')
channel.start_consuming()
