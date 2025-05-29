# Pass
This project, is a simple password sharing app.
User A adds a password, this is encrypted and stored in the database. The encryption key, generated on the fly, is returned to the user as a link and NOT stored. User B uses the key the link to access a page that decrypts the password and displays it.

## Features
- Passwords are single use, or can have a valid_until date.
- Passwords are encrypted and deleted after use.
- Rate limiting on the link to decrypt passwords.
- Password is censored in a modal, with button to reveal it and a button to copy it.

## Technical Implementation
- Uses Post-Redirect-Get (PRG) pattern to prevent duplicate secret creation on browser refresh
- Encryption keys are temporarily stored in session flash data for the share page
- Secrets have dedicated share URLs that are only accessible after creation
