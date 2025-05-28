# Pass - Secure Password Sharing

A simple, secure password sharing application built with Laravel. Share passwords safely with automatic encryption, single-use links, and time-based expiration.

## 🔐 Features

- **Secure Encryption**: Passwords are encrypted with dynamically generated keys
- **Single-Use Links**: Passwords are automatically deleted after being accessed
- **Time-Based Expiration**: Set custom expiration dates for shared passwords
- **Rate Limiting**: Protection against brute force attacks on decryption links
- **Privacy-First**: Encryption keys are never stored on the server
- **User-Friendly Interface**: Censored password display with reveal and copy functionality

## 🚀 How It Works

1. **User A** enters a password into the application
2. The password is encrypted using a dynamically generated key
3. The encrypted password is stored in the database
4. User A receives a secure link containing the decryption key (not stored on server)
5. **User B** uses the link to access and decrypt the password
6. The password is automatically deleted after use or expiration

## 📋 Requirements

- PHP ^8.2
- Composer
- Node.js & NPM
- Database (MySQL, PostgreSQL, SQLite, etc.)

## 🛠️ Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd pass
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure your database**
   Edit the `.env` file with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pass
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run database migrations**
   ```bash
   php artisan migrate
   ```

7. **Build assets**
   ```bash
   npm run build
   ```

## 🏃‍♂️ Running the Application

### Development
```bash
composer run dev
```
This command starts the Laravel server, queue worker, logs, and Vite development server concurrently.

### Production
```bash
php artisan serve
```

## 🧪 Testing

Run the test suite:
```bash
composer run test
```

## 🔒 Security Features

- **No Key Storage**: Encryption keys are never stored on the server
- **Automatic Cleanup**: Passwords are deleted after use or expiration
- **Rate Limiting**: Protection against automated attacks
- **Secure Encryption**: Industry-standard encryption algorithms
- **HTTPS Recommended**: Always use HTTPS in production

## 📖 Usage

### Creating a Shared Password

1. Navigate to the application homepage
2. Enter the password you want to share
3. Optionally set an expiration date
4. Click "Generate Secure Link"
5. Share the generated link with the intended recipient

### Accessing a Shared Password

1. Click on the shared link
2. The password will be displayed (initially censored)
3. Click "Reveal" to show the password
4. Use "Copy" to copy it to your clipboard
5. The password is automatically deleted after viewing

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## ⚠️ Important Security Notes

- Always use HTTPS in production environments
- Regularly update dependencies to patch security vulnerabilities
- Consider implementing additional authentication for sensitive environments
- Monitor access logs for suspicious activity
- Set appropriate rate limiting based on your use case

## 🛡️ Privacy

- No passwords are stored in plain text
- Encryption keys are never logged or stored
- Access logs contain no sensitive information
- Passwords are permanently deleted after use or expiration

