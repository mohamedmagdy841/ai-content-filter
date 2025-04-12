from fastapi import APIRouter, HTTPException
from schemas.content import ContentData
from openai import OpenAI
from transformers import pipeline
import os

router = APIRouter()

@router.post('/analyze')
def analyze(data: ContentData):
    user_input = f"Title: {data.title}\nContent: {data.content}"

    if data.ai_model == "gpt-3.5-turbo":
        client = OpenAI(api_key=os.environ.get("OPENAI_API_KEY"))
    elif data.ai_model == "deepseek-chat":
        client = OpenAI(api_key=os.environ.get("DEEPSEEK_API_KEY"), base_url=os.environ.get("DEEPSEEK_BASE_URL"))
    elif data.ai_model == "toxic-bert":
        toxic_classifier = pipeline("text-classification", model="unitary/toxic-bert")
    else:
        raise HTTPException(status_code=400, detail="Invalid AI model specified")
    
    try:
        score = None
        if data.ai_model == "toxic-bert":
            
            result = toxic_classifier(user_input)
            is_flagged = result[0]['label'] == 'toxic' and result[0]['score'] > 0.1
            score = result[0]["score"]
            reason = "Contains inappropriate content" if is_flagged else "Clean"
            
        else:
            
            response = client.chat.completions.create(
                model=data.ai_model,
                messages=[
                    {"role": "system", "content": (
                        "You are a strict content moderation bot. "
                        "Analyze the following text for hate speech, violence, explicit language, or any inappropriate content. "
                        "Respond only with 'Flagged: Yes/No' and a short reason."
                    )},
                    {"role": "user", "content": user_input}
                ]
            )
            
            ai_reply = response.choices[0].message.content.lower()

            is_flagged = "flagged: yes" in ai_reply
            reason = ai_reply.replace("flagged: yes", "").replace("flagged: no", "").strip()

        return {
            "is_flagged": is_flagged,
            "reason": reason if reason else ("Contains inappropriate content" if is_flagged else "Clean"),
            "score": score if score else None
        }

    except Exception as e:
        raise HTTPException(status_code=500, detail=f"AI Model Error: {str(e)}")
