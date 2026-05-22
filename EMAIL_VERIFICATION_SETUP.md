# Email Verification & Auto-Redirect Setup

## Overview

The email verification flow now supports automatic redirect to your frontend app after email verification.

## Flow

1. **User registers** via `/api/v1/auth/register`
   - Backend creates a temporary user record
   - Sends verification email with a signed link

2. **User clicks email link**
   - Link points to: `/api/v1/auth/email/verify-temp/{id}/{token}`
   - If clicked from email client (browser):
     - Returns HTML page that stores the auth token in localStorage
     - Auto-redirects to frontend app with the token
   - If API request with JSON accept header:
     - Returns JSON response with access_token

3. **Frontend receives redirect**
   - Token is stored in localStorage
   - User is logged in automatically
   - Frontend redirects to dashboard/home

## Configuration

Add to your `.env` file:

```env
# Frontend application URL for email verification redirects
FRONTEND_URL=http://localhost:3000
# or production URL:
FRONTEND_URL=https://app.yourdomain.com
```

The default value is `http://localhost:3000` if not set.

## API Endpoints

### Register
```
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass123"
}

Response: 201
{
  "message": "Registration received. Please check your email to verify your account."
}
```

### Verify Email (via email link)
```
GET /api/v1/auth/email/verify-temp/{id}/{token}

Response: 200 (HTML with JavaScript redirect + localStorage)
- Stores auth_token in localStorage
- Stores user data in localStorage
- Redirects to: FRONTEND_URL/auth/verify?token=...
```

### Verify Email (via API)
```
GET /api/v1/auth/email/verify-temp/{id}/{token}
Accept: application/json

Response: 200
{
  "message": "Email verified and account created.",
  "access_token": "token_here",
  "token_type": "Bearer",
  "user": { ... }
}
```

## Frontend Implementation

### 1. Handle the redirect after email verification

```javascript
// pages/auth/verify.tsx (or similar)
import { useEffect } from 'react';
import { useRouter } from 'next/router'; // or appropriate router

export default function VerifyPage() {
  const router = useRouter();

  useEffect(() => {
    // Check if token is in localStorage (set by email verification redirect)
    const token = localStorage.getItem('auth_token');
    const user = localStorage.getItem('user');

    if (token) {
      // Token is already set, redirect to dashboard
      setTimeout(() => {
        router.push('/dashboard');
      }, 1000);
    } else {
      // Manual verification or error
      router.push('/login');
    }
  }, [router]);

  return (
    <div style={{ textAlign: 'center', padding: '50px' }}>
      <h2>Email Verified!</h2>
      <p>Redirecting you to your dashboard...</p>
    </div>
  );
}
```

### 2. Update API interceptor to include auth token

```javascript
// api/client.ts or similar
import axios from 'axios';

const API_CLIENT = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000'
});

// Add auth token to all requests
API_CLIENT.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default API_CLIENT;
```

### 3. Retrieve stored user data after verification

```javascript
// hooks/useAuth.ts or similar
export function useAuth() {
  const [user, setUser] = useState(null);

  useEffect(() => {
    // Try to load from localStorage first
    const storedUser = localStorage.getItem('user');
    if (storedUser) {
      setUser(JSON.parse(storedUser));
    }
  }, []);

  return { user };
}
```

## Resend Verification Email

```
POST /api/v1/auth/email/resend
Content-Type: application/json

{
  "email": "user@example.com"
}

Response: 200
{
  "message": "Verification link resent."
}
```

## Testing

### Test Registration
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "TestPass123"
  }'
```

### Test Email Verification (JSON)
```bash
curl -X GET "http://localhost:8000/api/v1/auth/email/verify-temp/1/token_here" \
  -H "Accept: application/json"
```

### Test Email Verification (HTML Redirect)
Visit the link directly in your browser from the email.

## Security Notes

- Verification tokens expire after 60 minutes
- Tokens are hashed in the database (SHA256)
- Temporary user records are deleted after successful verification
- Signed URLs prevent tampering with ID and token parameters
- Auth tokens follow Laravel Sanctum security standards

## Troubleshooting

1. **Email not received**: Check mail configuration in `config/mail.php` and `.env`
2. **Link expired**: Resend verification email if older than 60 minutes
3. **Token not in localStorage**: Check browser console for JavaScript errors in email client
4. **Frontend not redirecting**: Verify `FRONTEND_URL` is set correctly in `.env`
