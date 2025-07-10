# Vito - Secure Secret Sharing

[![Tests](https://github.com/GlobusGroup/vito/actions/workflows/tests.yml/badge.svg)](https://github.com/GlobusGroup/vito/actions/workflows/tests.yml) [![Docker Pulls](https://img.shields.io/docker/pulls/globusgroup/vito)](https://hub.docker.com/r/globusgroup/vito) [![license](https://img.shields.io/github/license/dec0dOS/amazing-github-template.svg)](LICENSE) <svg xmlns="http://www.w3.org/2000/svg" style="margin-left:5px;" width="215" height="20" role="img" aria-label="Globus Group: Open Source"><title>Globus Group: Open Source</title><linearGradient id="s" x2="0" y2="100%"><stop offset="0" stop-color="#bbb" stop-opacity=".1"/><stop offset="1" stop-opacity=".1"/></linearGradient><clipPath id="r"><rect x="25" width="190" height="20" rx="3" fill="#fff"/></clipPath><g clip-path="url(#r)"><rect x="25" width="100" height="20" fill="#1B1E2A"/><rect x="125" width="90" height="20" fill="#006FB9"/><rect x="25" width="190" height="20" fill="url(#s)"/></g><g fill="#fff" text-anchor="middle" font-family="Verdana,Geneva,DejaVu Sans,sans-serif" text-rendering="geometricPrecision" font-size="110"><text aria-hidden="true" x="755" y="150" fill="#010101" fill-opacity=".3" transform="scale(.1)" textLength="700">Globus Group</text><text x="755" y="140" transform="scale(.1)" fill="#fff" textLength="700">Globus Group</text><text aria-hidden="true" x="1705" y="150" fill="#010101" fill-opacity=".3" transform="scale(.1)" textLength="800">Open Source</text><text x="1705" y="140" transform="scale(.1)" fill="#fff" textLength="800">Open Source</text></g><g transform="scale(0.253164)"><path d="M21.9305 19.3418L1.52268 54.7264C1.52268 54.7264 1.19353 55.3858 1.08381 55.4956C-1.98833 61.4297 2.29072 68.5726 8.98359 68.5726H11.5071L21.9305 50.5506C27.5261 40.8803 27.5261 29.0121 21.9305 19.3418Z" fill="#006FB9"></path><path d="M14.6895 68.5733H55.2856C55.2856 68.5733 56.3828 68.5733 56.6022 68.5733C63.0756 68.1337 67.0255 60.9908 63.7339 55.1667L62.1979 52.5293H41.8998C30.8182 52.5293 20.5046 58.4634 14.9089 68.1337L14.6895 68.5733Z" fill="#006FB9"></path><path d="M60.6616 49.7812C60.6616 49.7812 41.0219 15.6053 40.583 15.0559C40.2538 14.5064 39.9247 13.957 39.7052 13.6273C35.9748 8.46248 28.075 8.68226 24.7834 14.3965L23.4668 16.5943L33.561 34.1768C39.1566 43.8471 49.4703 49.7812 60.6616 49.7812Z" fill="#006FB9"></path></g></svg>



Vito is a secure, self-hosted secret sharing application built with Laravel. It allows users to share sensitive information like passwords, API keys, and confidential text through encrypted, single-use links that automatically expire.

![Vito Interface](https://github.com/GlobusGroup/vito/blob/main/.github/images/vito_screenshot_1.png?raw=true)


## Security Features

- **End-to-End Encryption**: Secrets are encrypted using AES-256-CBC with PBKDF2 key derivation
- **Single-Use Links**: Each secret can only be accessed once and is permanently destroyed after viewing
- **Automatic Expiration**: All secrets automatically expire after 1 hour (configurable)
- **Optional Password Protection**: optionally add an additional password layer for extra security
- **Zero-Knowledge Architecture**: Encryption keys are embedded in URLs, not stored on the server
- **Rate Limiting**: Built-in protection against brute force attacks
- **HMAC Verification**: Ensures data integrity and authenticity

## Deploy With Docker

Vito includes a production-ready Docker setup using the pre-built image.

1. **Download or copy/paste the production compose file**
   ```bash
   wget https://raw.githubusercontent.com/GlobusGroup/vito/main/docker-compose.prod.yml -O docker-compose.yml
   ```

   ```yaml
   services:
      app:
        image: globusgroup/vito:latest
        restart: unless-stopped
        volumes:
          - ./storage:/var/www/html/storage
        ports:
          - "9998:80"
    ```

2. **Create storage directory**
   ```bash
   mkdir -p storage
   ```

3. **Deploy with Docker Compose**
   ```bash
   docker compose  up -d
   ```

4. **Configure your reverse proxy**
   
   The application will be available on port 9998. **Important**: Vito MUST be run behind a reverse proxy that provides HTTPS support (such as Nginx, Caddy, or Traefik).

   Example Caddy configuration (Caddyfile):
   ```
   your-domain.com {
       reverse_proxy localhost:9998
   }
   ```
   
   Caddy automatically handles HTTPS certificates via Let's Encrypt, making it the simplest option for deployment.

## Features

- Clean, modern web interface built with Tailwind CSS
- Mobile-responsive design
- Character count and validation
- Secure random key generation
- Database-agnostic (SQLite by default, supports MySQL, PostgreSQL)
- Docker support for easy deployment

## Requirements

- PHP 8.2 or higher
- Composer
- SQLite/MySQL/PostgreSQL (SQLite included by default)

## Installation

### Option 1: Docker Development Setup

1. **Clone the repository**
   ```bash
   git clone git@github.com:GlobusGroup/vito.git
   cd vito
   ```

2. **Start the development environment**
   ```bash
   docker compose up -d
   ```

3. **Access the application**
   - Application: http://localhost:8080

The Docker development setup includes:
- Caddy web server
- Vite development server with hot reload
- Automatic volume mounting for live code changes

### Option 2: Local Development Without Docker

1. **Clone the repository**
   ```bash
   git clone git@github.com:GlobusGroup/vito.git
   cd vito
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Set up environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Set up database**
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

6. **Build frontend assets**
   ```bash
   npm run build
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

   Or use the convenient development script that starts all services:
   ```bash
   composer run dev
   ```

   This starts:
   - Laravel development server
   - Queue worker
   - Log viewer (Pail)
   - Vite development server

8. **Access the application**
   - Application: http://localhost:8000

## Manual Production Deployment

For traditional server deployment:

1. **Clone and set up the application**
   ```bash
   git clone git@github.com:GlobusGroup/vito.git
   cd vito
   composer install --no-dev --optimize-autoloader
   ```

2. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your production settings
   php artisan key:generate
   ```

3. **Set up database and permissions**
   ```bash
   php artisan migrate --force
   chown -R www-data:www-data storage bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

## Configuration

### Environment Variables

Key configuration options in `.env`:

```env
APP_NAME=Vito
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Secret lifetime in minutes (default: 60)
SECRETS_LIFETIME_IN_MINUTES=60

# Database configuration
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite

# For MySQL/PostgreSQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=vito
# DB_USERNAME=vito
# DB_PASSWORD=password
```

### Security Considerations

- Always use HTTPS in production
- Set `APP_DEBUG=false` in production
- Use a strong, random `APP_KEY`
- Consider setting a shorter `SECRETS_LIFETIME_IN_MINUTES` for highly sensitive environments
- Regularly update dependencies
- Monitor logs for suspicious activity

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
```bash
./vendor/bin/pint
```

## API

Vito provides a REST API for programmatic secret creation.

### Create Secret

**Endpoint:** `POST /api/v1/secrets`

**Headers:**
- `Content-Type: application/json`
- `Accept: application/json`

**Request Body:**
```json
{
  "content": "Your secret content here",
  "password": "optional_password",
  "expires_in_minutes": 120
}
```

**Parameters:**
- `content` (required): The secret content to encrypt (max 200,000 characters)
- `password` (optional): Additional password protection (max 100 characters)
- `expires_in_minutes` (optional): Custom expiry time in minutes (1-10080, default: 60)

**Response:**
```json
{
  "success": true,
  "data": {
    "share_url": "https://your-domain.com/secrets/show?d=encrypted_data",
    "expires_at": "2025-06-02T12:00:00.000000Z",
    "expires_on": "02/06/2025 12:00",
    "expires_in_minutes": 120,
    "requires_password": true
  }
}
```

**Example:**
```bash
curl -X POST https://your-domain.com/api/v1/secrets \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "content": "My secret API key: sk-1234567890",
    "password": "mypassword",
    "expires_in_minutes": 30
  }'
```

**Rate Limiting:** 60 requests per minute per IP address.

## How It Works

1. **Secret Creation**: User enters sensitive content and optional password
2. **Encryption**: Content is encrypted with AES-256-CBC using a random 256-bit key
3. **Storage**: Only the encrypted content is stored in the database
4. **URL Generation**: The encryption key is embedded in a unique sharing URL
5. **Sharing**: The complete URL is shown once and never stored
6. **Access**: Recipients use the URL to decrypt and view the secret
7. **Destruction**: The secret is permanently deleted after first access or expiration

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and ensure code style compliance
5. Submit a pull request

## Licence

This project is licensed under the MIT Licence.

## Important Security Notes

- **HTTPS Required**: Always run Vito behind a reverse proxy with HTTPS in production
- **Single Use**: Each secret link works only once - save it immediately after creation
- **No Recovery**: Lost links cannot be recovered - the encryption key exists only in the URL
- **Expiration**: All secrets expire automatically after the configured time limit
- **Zero Knowledge**: Server administrators cannot decrypt secrets without the sharing URLs

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/GlobusGroup/vito).
