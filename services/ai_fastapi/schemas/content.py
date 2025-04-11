from pydantic import BaseModel

class ContentData(BaseModel):
    title: str
    content: str
    ai_model: str
