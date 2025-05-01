# AI-Powered Content Filtering API

This project is a full-featured Laravel + FastAPI backend for a social platform that automatically filters toxic and hateful content from posts and comments using AI, with asynchronous communication handled via the RabbitMQ message broker.

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
    <td align="center">
      <a href="https://www.rabbitmq.com" target="_blank">
        <img src="https://github.com/user-attachments/assets/5dad1de3-8dbc-4beb-84d2-1d6963982696" width="350" alt="RabbitMQ Logo" />
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
- **RabbitMQ Integration** – Asynchronous communication between Laravel and FastAPI for scalable message-driven architecture
- **Filament Admin Dashboard** – A modern admin panel for managing users, flagged content, and system logs
- **Tags Support** – Posts and comments can be tagged for better organization
- **Real-time Notifications** – Flagged content and moderation decisions trigger Laravel notifications
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
| RabbitMQ       | Message queue for async processing |
| OpenAI / DeepSeek / Toxic-BERT | Natural language understanding & filtering |
| Filament       | Admin dashboard                   |
| MySQL          | Database                          |
| PHPUnit        | Feature testing                   |

---

## How It Works

1. User creates a **post** or **comment** in Laravel
2. Laravel pushes the content payload to RabbitMQ (`post.analysis.request`)
3. FastAPI consumes the message, analyzes it using the chosen AI model
4. FastAPI sends the result to another RabbitMQ queue (`post.analysis.response`)
5. Laravel listens for the result and updates the content’s status
6. Flagged content is logged, and notifications are dispatched
7. Every week/month, a **pruning command** runs to delete old flagged data

```
User submits content
        ↓
Laravel pushes message to RabbitMQ ➜ FastAPI consumes
        ↓                                     ↓
Content Analyzed                  Analysis result returned via RabbitMQ
        ↓                                     ↓
Laravel updates status            ➜ APPROVED or FLAGGED with reason/score
        ↓
If flagged ➜ Log it, notify admin
```

---

## Scheduled Pruning

```php
Schedule::command('model:prune')->daily();
```

---

## AI Service Communication (Old - HTTP-based, now replaced by RabbitMQ)

> This section is for legacy reference.

- Laravel **used to** call FastAPI via HTTP (`POST /analyze`)
  ```json
  {
    "title": "I hate you",
    "content": "bad words",
    "ai_model": "toxic-bert"
  }
  ```

- FastAPI responded with:
  ```json
  {
    "is_flagged": true,
    "reason": "Hateful language detected",
    "confidence": 0.92
  }
  ```

> ⚠️ This flow is now **replaced** with **RabbitMQ queues** for async and decoupled communication.
