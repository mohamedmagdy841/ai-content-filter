# AI-Powered Content Filtering API

This project is a full-featured Laravel + FastAPI backend for a social platform that automatically filters toxic and hateful content from posts and comments using AI.

<table>
  <tr>
    <td align="center">
      <a href="https://laravel.com" target="_blank">
        <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="350" alt="Laravel Logo" />
      </a>
    </td>
    <td align="center">
      <a href="https://fastapi.tiangolo.com" target="_blank">
        <img src="https://github.com/user-attachments/assets/45411bb2-ac14-400c-a868-9c9c287a3136" width="350" alt="FastAPI Logo" />
      </a>
    </td>
  </tr>
</table>

## Features

- **Microservices Architecture**
  - Laravel handles core features (auth, posts, comments, logs, etc.)
  - FastAPI handles AI content analysis separately
- **AI Integration** with:
  - OpenAI
  - DeepSeek
  - Toxic-BERT ([HuggingFace](https://huggingface.co/unitary/toxic-bert))
- **Unit Testing** – Reliable and clean test coverage using PHPUnit  
- **Rate Limiting** – Prevent abuse and spam with Laravel’s built-in rate limiting  
- **Automatic Content Filtering**
  - Flags posts and comments that include toxic or hateful content
  - Provides reasons and confidence scores when flagged
- **Model Pruning**
  - Automatically deletes old flagged posts and comments weekly or monthly
- **API Versioning**
  - Maintain multiple API versions with clear structure and scalability

---

### Postman Documentation [here](https://documenter.getpostman.com/view/38857071/2sB2cYeM7v)

---

## Tech Stack

| Technology     | Purpose                           |
|----------------|-----------------------------------|
| Laravel 12     | Core API backend                  |
| FastAPI        | AI-based content analysis service |
| OpenAI / DeepSeek / Toxic-BERT | Natural language understanding & filtering |
| MySQL          | Database                          |
| PHPUnit        | Feature testing                   |

---

## How It Works

1. User creates a **post** or **comment**
2. The content is sent to the **FastAPI service** for analysis
3. Based on the AI model’s output:
   - Content is marked as `APPROVED`, `FLAGGED`, or `PENDING`
   - Flagged content stores the reason and confidence score
4. Every week/month, a **pruning command** runs to delete old flagged data
5. Only the content owner can edit, delete, or restore their post/comment

 ```
User submits content ➜ AI Service analyzes ➜ Content status assigned
                                 |
                 ┌───────────────┴───────────────┐
                 |                               |
           Content is OK                 Content is Flagged
           ➜ APPROVED                   ➜ FLAGGED
                                         ➜ Saved in Filter Logs
```  


---

## Scheduled Pruning

```php
Schedule::command('model:prune')->daily();
```

---

## AI Service Communication

- Laravel calls FastAPI via HTTP (`POST /analyze`)
  ```json
  {
    "title": "I hate you",
    "content": "bad words",
    "ai_model": "toxic-bert"
  }
- FastAPI responds with:
  ```json
  {
    "is_flagged": true,
    "reason": "Hateful language detected",
    "confidence": 0.92
  }
  ```
