<?php

namespace App\Controllers;

use App\Models\EmailVerificationTokenModel;
use App\Models\PasswordResetTokenModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class AuthController extends BaseController
{
    public function register(): string|RedirectResponse
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/register', [
            'title' => 'Daftar - Ada Acara',
        ]);
    }

    public function attemptRegister(): RedirectResponse
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[120]',
            'email' => 'required|valid_email|max_length[190]|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $created = $userModel->insert([
            'name' => trim((string) $this->request->getPost('name')),
            'email' => $email,
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'email_verified_at' => null,
        ]);

        if (! $created) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $userModel->errors());
        }

        $userId = (int) $created;
        $name = trim((string) $this->request->getPost('name'));
        if ($this->emailVerificationStorageReady()) {
            try {
                $token = $this->createEmailVerificationToken($userId, $email);
                $this->sendEmailVerificationEmail($email, $name, $token);
            } catch (\Throwable $exception) {
                log_message('error', 'Email verifikasi gagal dikirim ke {email}: {message}', [
                    'email' => $email,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return redirect()->to('/verify-email?email=' . rawurlencode($email))
            ->with('success', 'Akun berhasil dibuat. Cek email untuk verifikasi sebelum login.');
    }

    public function login(): string|RedirectResponse
    {
        $redirect = $this->safeEditorRedirect((string) ($this->request->getGet('redirect') ?? ''));

        if (session()->get('isLoggedIn')) {
            return redirect()->to($redirect !== '' ? $redirect : '/dashboard');
        }

        return view('auth/login', [
            'title' => 'Login - Ada Acara',
            'redirect' => $redirect,
        ]);
    }

    public function attemptLogin(): RedirectResponse
    {
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', strtolower(trim((string) $this->request->getPost('email'))))->first();

        if (! $user || ! password_verify((string) $this->request->getPost('password'), $user['password_hash'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Email atau password tidak sesuai.');
        }

        if ($this->userRequiresEmailVerification($user)) {
            return redirect()->to('/verify-email?email=' . rawurlencode((string) $user['email']))
                ->with('error', 'Email belum diverifikasi. Cek inbox atau kirim ulang email verifikasi.');
        }

        $this->loginUser($user);

        $redirect = $this->safeEditorRedirect((string) ($this->request->getPost('redirect') ?? ''));
        if ($redirect !== '') {
            return redirect()->to($redirect);
        }

        return redirect()->to($this->isAdminRole((string) ($user['role'] ?? 'user')) ? '/admin' : '/dashboard');
    }

    public function googleRedirect(): RedirectResponse
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        $clientId = trim((string) env('GOOGLE_CLIENT_ID', ''));
        $redirectUri = trim((string) env('GOOGLE_REDIRECT_URI', site_url('auth/google/callback')));

        if ($clientId === '') {
            return redirect()->to('/login')->with('error', 'Login Google belum dikonfigurasi.');
        }

        $redirect = $this->safeEditorRedirect((string) ($this->request->getGet('redirect') ?? ''));
        $state = $this->createGoogleOAuthState($redirect);
        session()->set('google_oauth_state', $state);

        if ($redirect !== '') {
            session()->set('google_oauth_redirect', $redirect);
        }

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);

        return redirect()->to('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    public function googleCallback(): RedirectResponse
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        $state = (string) ($this->request->getGet('state') ?? '');
        $expectedState = (string) (session()->get('google_oauth_state') ?? '');
        session()->remove('google_oauth_state');
        $signedStatePayload = $this->readGoogleOAuthState($state);
        $sessionStateValid = $state !== '' && $expectedState !== '' && hash_equals($expectedState, $state);
        $signedStateValid = is_array($signedStatePayload);

        if (! $sessionStateValid && ! $signedStateValid) {
            return redirect()->to('/login')->with('error', 'Login Google tidak valid. Coba lagi.');
        }

        $code = (string) ($this->request->getGet('code') ?? '');
        if ($code === '') {
            return redirect()->to('/login')->with('error', 'Login Google dibatalkan atau tidak lengkap.');
        }

        try {
            $profile = $this->googleProfileFromCode($code);
            if ($profile === null) {
                return redirect()->to('/login')->with('error', 'Profil Google tidak bisa dibaca.');
            }

            if (empty($profile['email_verified'])) {
                return redirect()->to('/login')->with('error', 'Email Google belum terverifikasi.');
            }

            $user = $this->findOrCreateGoogleUser($profile);
            $this->loginUser($user);
        } catch (\Throwable $exception) {
            log_message('error', 'Login Google gagal: {message}', [
                'message' => $exception->getMessage(),
            ]);
            return redirect()->to('/login')->with('error', 'Login Google belum berhasil. Coba lagi nanti.');
        }

        $redirect = $this->safeEditorRedirect((string) (session()->get('google_oauth_redirect') ?? ''));
        if ($redirect === '' && $signedStatePayload !== null) {
            $redirect = $this->safeEditorRedirect((string) ($signedStatePayload['redirect'] ?? ''));
        }
        session()->remove('google_oauth_redirect');

        if ($redirect !== '') {
            return redirect()->to($redirect);
        }

        return redirect()->to($this->isAdminRole((string) ($user['role'] ?? 'user')) ? '/admin' : '/dashboard');
    }

    private function isAdminRole(string $role): bool
    {
        return in_array(strtolower(trim($role)), ['superadmin', 'admin', 'finance_admin', 'content_admin', 'support_admin'], true);
    }

    public function logout(): RedirectResponse
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        session()->destroy();

        return redirect()->to('/login')->with('success', 'Kamu sudah logout.');
    }

    public function verificationNotice(): string|RedirectResponse
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        $email = strtolower(trim((string) ($this->request->getGet('email') ?? '')));

        return view('auth/verify_email_notice', [
            'title' => 'Verifikasi Email - Ada Acara',
            'email' => $email,
        ]);
    }

    public function resendVerificationEmail(): RedirectResponse
    {
        if (! $this->validate(['email' => 'required|valid_email|max_length[190]'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        $user = (new UserModel())->where('email', $email)->first();

        if ($user !== null && $this->userRequiresEmailVerification($user)) {
            if (! $this->emailVerificationStorageReady()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Fitur verifikasi email belum siap. Hubungi admin.');
            }

            try {
                $token = $this->createEmailVerificationToken((int) $user['id'], $email);
                if (! $this->sendEmailVerificationEmail($email, (string) ($user['name'] ?? 'Pengguna AdaAcara'), $token)) {
                    throw new \RuntimeException('Email verification provider send returned false.');
                }
            } catch (\Throwable $exception) {
                log_message('error', 'Kirim ulang email verifikasi gagal untuk {email}: {message}', [
                    'email' => $email,
                    'message' => $exception->getMessage(),
                ]);

                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Email verifikasi belum bisa dikirim. Periksa konfigurasi Brevo atau coba lagi nanti.');
            }
        }

        return redirect()->to('/verify-email?email=' . rawurlencode($email))
            ->with('success', 'Jika email perlu diverifikasi, link verifikasi sudah dikirim ulang.');
    }

    public function verifyEmail(string $token): RedirectResponse
    {
        if (! $this->emailVerificationStorageReady()) {
            return redirect()->to('/login')->with('error', 'Fitur verifikasi email belum siap. Hubungi admin.');
        }

        $verification = $this->validEmailVerificationToken($token);
        if ($verification === null) {
            return redirect()->to('/login')->with('error', 'Link verifikasi email tidak valid atau sudah kedaluwarsa.');
        }

        $now = date('Y-m-d H:i:s');
        (new UserModel())->update((int) $verification['user_id'], [
            'email_verified_at' => $now,
        ]);

        db_connect()->table('email_verification_tokens')
            ->where('user_id', (int) $verification['user_id'])
            ->where('used_at IS NULL', null, false)
            ->update(['used_at' => $now]);

        return redirect()->to('/login')->with('success', 'Email berhasil diverifikasi. Silakan login.');
    }

    public function forgotPassword(): string|RedirectResponse
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/forgot_password', [
            'title' => 'Lupa Password - Ada Acara',
        ]);
    }

    public function sendPasswordReset(): RedirectResponse
    {
        if (! $this->validate(['email' => 'required|valid_email|max_length[190]'])) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        if ($this->passwordResetRateLimited($email)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terlalu banyak permintaan reset. Coba lagi beberapa menit lagi.');
        }

        $user = (new UserModel())->where('email', $email)->first();
        if ($user !== null) {
            if (! $this->passwordResetStorageReady()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Fitur reset password belum siap. Hubungi admin untuk mengaktifkan tabel reset password.');
            }

            try {
                $token = $this->createPasswordResetToken($email);
                if (! $this->sendPasswordResetEmail($email, (string) ($user['name'] ?? 'Pengguna AdaAcara'), $token)) {
                    throw new \RuntimeException('Email provider send returned false.');
                }
            } catch (\Throwable $exception) {
                log_message('error', 'Reset password gagal diproses untuk {email}: {message}', [
                    'email' => $email,
                    'message' => $exception->getMessage(),
                ]);

                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Email reset belum bisa dikirim. Periksa konfigurasi Brevo atau coba lagi nanti.');
            }
        }

        return redirect()->to('/login')->with('success', 'Jika email terdaftar, link reset password sudah dikirim.');
    }

    public function resetPassword(string $token): string|RedirectResponse
    {
        if (! $this->passwordResetStorageReady()) {
            return redirect()->to('/forgot-password')->with('error', 'Fitur reset password belum siap. Hubungi admin untuk mengaktifkan tabel reset password.');
        }

        $reset = $this->validPasswordResetToken($token);
        if ($reset === null) {
            return redirect()->to('/forgot-password')->with('error', 'Link reset password tidak valid atau sudah kedaluwarsa.');
        }

        return view('auth/reset_password', [
            'title' => 'Reset Password - Ada Acara',
            'token' => $token,
        ]);
    }

    public function updatePassword(string $token): RedirectResponse
    {
        if (! $this->passwordResetStorageReady()) {
            return redirect()->to('/forgot-password')->with('error', 'Fitur reset password belum siap. Hubungi admin untuk mengaktifkan tabel reset password.');
        }

        $reset = $this->validPasswordResetToken($token);
        if ($reset === null) {
            return redirect()->to('/forgot-password')->with('error', 'Link reset password tidak valid atau sudah kedaluwarsa.');
        }

        $rules = [
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', (string) $reset['email'])->first();
        if ($user === null) {
            return redirect()->to('/forgot-password')->with('error', 'Akun tidak ditemukan.');
        }

        $userModel->update((int) $user['id'], [
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
        ]);

        $resetModel = new PasswordResetTokenModel();
        db_connect()->table('password_reset_tokens')
            ->where('email', (string) $reset['email'])
            ->where('used_at IS NULL', null, false)
            ->update(['used_at' => date('Y-m-d H:i:s')]);

        return redirect()->to('/login')->with('success', 'Password berhasil diperbarui. Silakan login.');
    }

    private function safeEditorRedirect(string $redirect): string
    {
        $redirect = trim(rawurldecode($redirect));
        if ($redirect === '') {
            return '';
        }

        $path = parse_url($redirect, PHP_URL_PATH);
        if (! is_string($path) || ! preg_match('#^/(?:editor|templates/preview)/[1-9][0-9]*$#', $path)) {
            return '';
        }

        return $path;
    }

    private function createGoogleOAuthState(string $redirect = ''): string
    {
        $key = $this->googleOAuthStateKey();
        if ($key === '') {
            return bin2hex(random_bytes(24));
        }

        $payload = [
            'iat' => time(),
            'nonce' => bin2hex(random_bytes(16)),
            'redirect' => $redirect,
        ];
        $encodedPayload = $this->base64UrlEncode((string) json_encode($payload, JSON_UNESCAPED_SLASHES));
        $signature = hash_hmac('sha256', $encodedPayload, $key, true);

        return $encodedPayload . '.' . $this->base64UrlEncode($signature);
    }

    private function readGoogleOAuthState(string $state): ?array
    {
        $key = $this->googleOAuthStateKey();
        if ($key === '' || ! str_contains($state, '.')) {
            return null;
        }

        [$encodedPayload, $encodedSignature] = explode('.', $state, 2);
        $signature = $this->base64UrlDecode($encodedSignature);
        if ($signature === null) {
            return null;
        }

        $expectedSignature = hash_hmac('sha256', $encodedPayload, $key, true);
        if (! hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $payloadJson = $this->base64UrlDecode($encodedPayload);
        $payload = $payloadJson !== null ? json_decode($payloadJson, true) : null;
        if (! is_array($payload)) {
            return null;
        }

        $issuedAt = (int) ($payload['iat'] ?? 0);
        if ($issuedAt < (time() - 900) || $issuedAt > (time() + 60)) {
            return null;
        }

        return $payload;
    }

    private function googleOAuthStateKey(): string
    {
        $clientSecret = trim((string) env('GOOGLE_CLIENT_SECRET', ''));
        if ($clientSecret !== '') {
            return $clientSecret;
        }

        $encryptionKey = (string) (config('Encryption')->key ?? '');
        return trim($encryptionKey);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): ?string
    {
        $remainder = strlen($value) % 4;
        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        return is_string($decoded) ? $decoded : null;
    }

    private function passwordResetRateLimited(string $email): bool
    {
        $key = 'password_reset_requests';
        $requests = session()->get($key);
        $requests = is_array($requests) ? $requests : [];
        $now = time();
        $window = 15 * 60;
        $fingerprint = hash('sha256', $this->request->getIPAddress() . '|' . $email);

        $requests = array_filter($requests, static fn (int $timestamp): bool => ($now - $timestamp) < $window);
        $matches = 0;
        foreach ($requests as $storedKey => $timestamp) {
            if (str_starts_with((string) $storedKey, $fingerprint . ':')) {
                $matches++;
            }
        }

        if ($matches >= 3) {
            session()->set($key, $requests);
            return true;
        }

        $requests[$fingerprint . ':' . bin2hex(random_bytes(3))] = $now;
        session()->set($key, $requests);

        return false;
    }

    private function loginUser(array $user): void
    {
        session()->regenerate(true);
        session()->set([
            'isLoggedIn' => true,
            'userId' => (int) $user['id'],
            'userName' => $user['name'],
            'userEmail' => $user['email'],
            'userRole' => $user['role'] ?? 'user',
        ]);
    }

    private function googleProfileFromCode(string $code): ?array
    {
        $clientId = trim((string) env('GOOGLE_CLIENT_ID', ''));
        $clientSecret = trim((string) env('GOOGLE_CLIENT_SECRET', ''));
        $redirectUri = trim((string) env('GOOGLE_REDIRECT_URI', site_url('auth/google/callback')));

        if ($clientId === '' || $clientSecret === '') {
            throw new \RuntimeException('Google OAuth credential belum lengkap.');
        }

        $client = service('curlrequest', [
            'timeout' => 15,
            'http_errors' => false,
        ]);

        $tokenResponse = $client->post('https://oauth2.googleapis.com/token', [
            'form_params' => [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ],
        ]);

        if ($tokenResponse->getStatusCode() < 200 || $tokenResponse->getStatusCode() >= 300) {
            log_message('error', 'Google token exchange gagal: status={status}, body={body}', [
                'status' => (string) $tokenResponse->getStatusCode(),
                'body' => mb_substr((string) $tokenResponse->getBody(), 0, 1000),
            ]);
            return null;
        }

        $tokenData = json_decode((string) $tokenResponse->getBody(), true);
        $accessToken = is_array($tokenData) ? (string) ($tokenData['access_token'] ?? '') : '';
        if ($accessToken === '') {
            return null;
        }

        $profileResponse = $client->get('https://www.googleapis.com/oauth2/v3/userinfo', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => 'application/json',
            ],
        ]);

        if ($profileResponse->getStatusCode() < 200 || $profileResponse->getStatusCode() >= 300) {
            log_message('error', 'Google userinfo gagal: status={status}, body={body}', [
                'status' => (string) $profileResponse->getStatusCode(),
                'body' => mb_substr((string) $profileResponse->getBody(), 0, 1000),
            ]);
            return null;
        }

        $profile = json_decode((string) $profileResponse->getBody(), true);
        if (! is_array($profile) || empty($profile['sub']) || empty($profile['email'])) {
            return null;
        }

        return $profile;
    }

    private function findOrCreateGoogleUser(array $profile): array
    {
        $userModel = new UserModel();
        $email = strtolower(trim((string) $profile['email']));
        $googleId = (string) $profile['sub'];
        $now = date('Y-m-d H:i:s');

        $user = $userModel->where('email', $email)->first();
        $payload = $this->filterUserColumns([
            'google_id' => $googleId,
            'avatar_url' => (string) ($profile['picture'] ?? ''),
            'email_verified_at' => $now,
        ]);

        if ($user !== null) {
            if (! empty($payload)) {
                $userModel->update((int) $user['id'], $payload);
                $user = $userModel->find((int) $user['id']) ?: $user;
            }

            return $user;
        }

        $createdId = (int) $userModel->insert($this->filterUserColumns([
            'name' => trim((string) ($profile['name'] ?? 'Pengguna Google')) ?: 'Pengguna Google',
            'email' => $email,
            'password_hash' => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
            'email_verified_at' => $now,
            'google_id' => $googleId,
            'avatar_url' => (string) ($profile['picture'] ?? ''),
            'role' => 'user',
        ]));

        $user = $userModel->find($createdId);
        if ($user === null) {
            throw new \RuntimeException('User Google gagal dibuat.');
        }

        return $user;
    }

    private function filterUserColumns(array $data): array
    {
        try {
            $fields = db_connect()->getFieldNames('users');
        } catch (\Throwable) {
            $fields = ['name', 'email', 'password_hash', 'email_verified_at', 'role'];
        }

        return array_intersect_key($data, array_flip($fields));
    }

    private function passwordResetStorageReady(): bool
    {
        try {
            return db_connect()->tableExists('password_reset_tokens');
        } catch (\Throwable $exception) {
            log_message('error', 'Cek tabel password_reset_tokens gagal: {message}', [
                'message' => $exception->getMessage(),
            ]);
            return false;
        }
    }

    private function emailVerificationStorageReady(): bool
    {
        try {
            $db = db_connect();
            return $db->tableExists('email_verification_tokens')
                && in_array('email_verified_at', $db->getFieldNames('users'), true);
        } catch (\Throwable $exception) {
            log_message('error', 'Cek storage verifikasi email gagal: {message}', [
                'message' => $exception->getMessage(),
            ]);
            return false;
        }
    }

    private function userRequiresEmailVerification(array $user): bool
    {
        if (! array_key_exists('email_verified_at', $user)) {
            return false;
        }

        return empty($user['email_verified_at']);
    }

    private function createEmailVerificationToken(int $userId, string $email): string
    {
        $selector = bin2hex(random_bytes(8));
        $validator = bin2hex(random_bytes(32));
        $now = date('Y-m-d H:i:s');

        db_connect()->table('email_verification_tokens')
            ->where('user_id', $userId)
            ->where('used_at IS NULL', null, false)
            ->update(['used_at' => $now]);

        (new EmailVerificationTokenModel())->insert([
            'user_id' => $userId,
            'email' => $email,
            'selector' => $selector,
            'token_hash' => hash('sha256', $validator),
            'expires_at' => date('Y-m-d H:i:s', time() + 86400),
            'used_at' => null,
            'created_at' => $now,
        ]);

        return $selector . '.' . $validator;
    }

    private function validEmailVerificationToken(string $token): ?array
    {
        if (! preg_match('/\A([a-f0-9]{16})\.([a-f0-9]{64})\z/i', $token, $matches)) {
            return null;
        }

        $verification = (new EmailVerificationTokenModel())->where('selector', strtolower($matches[1]))->first();
        if ($verification === null || ($verification['used_at'] ?? null) !== null) {
            return null;
        }

        if (strtotime((string) $verification['expires_at']) < time()) {
            return null;
        }

        if (! hash_equals((string) $verification['token_hash'], hash('sha256', strtolower($matches[2])))) {
            return null;
        }

        return $verification;
    }

    private function createPasswordResetToken(string $email): string
    {
        $selector = bin2hex(random_bytes(8));
        $validator = bin2hex(random_bytes(32));
        $now = date('Y-m-d H:i:s');

        db_connect()->table('password_reset_tokens')
            ->where('email', $email)
            ->where('used_at IS NULL', null, false)
            ->update(['used_at' => $now]);

        $resetModel = new PasswordResetTokenModel();
        $resetModel->insert([
            'email' => $email,
            'selector' => $selector,
            'token_hash' => hash('sha256', $validator),
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
            'used_at' => null,
            'created_at' => $now,
        ]);

        return $selector . '.' . $validator;
    }

    private function validPasswordResetToken(string $token): ?array
    {
        if (! preg_match('/\A([a-f0-9]{16})\.([a-f0-9]{64})\z/i', $token, $matches)) {
            return null;
        }

        $reset = (new PasswordResetTokenModel())->where('selector', strtolower($matches[1]))->first();
        if ($reset === null || ($reset['used_at'] ?? null) !== null) {
            return null;
        }

        if (strtotime((string) $reset['expires_at']) < time()) {
            return null;
        }

        if (! hash_equals((string) $reset['token_hash'], hash('sha256', strtolower($matches[2])))) {
            return null;
        }

        return $reset;
    }

    private function sendPasswordResetEmail(string $email, string $name, string $token): bool
    {
        $resetUrl = site_url('reset-password/' . $token);
        $emailConfig = config('Email');
        $fromEmail = (string) $emailConfig->fromEmail;
        $fromName = (string) ($emailConfig->fromName ?: 'AdaAcara');
        $html = view('emails/password_reset', [
            'name' => $name,
            'resetUrl' => $resetUrl,
        ]);
        $subject = 'Reset password AdaAcara';
        $apiKey = $this->brevoApiKey();

        if ($apiKey === '') {
            $this->logPasswordResetBrevoApiFailure('BREVO_API_KEY belum terbaca. Reset password memakai Brevo API, bukan SMTP.', [
                'from' => $fromEmail,
            ]);
            return false;
        }

        return $this->sendPasswordResetEmailViaBrevoApi(
            $apiKey,
            $email,
            $name,
            $fromEmail,
            $fromName,
            $subject,
            $html,
            $resetUrl
        );
    }

    private function sendEmailVerificationEmail(string $email, string $name, string $token): bool
    {
        $verifyUrl = site_url('verify-email/' . $token);
        $emailConfig = config('Email');
        $fromEmail = (string) $emailConfig->fromEmail;
        $fromName = (string) ($emailConfig->fromName ?: 'AdaAcara');
        $html = view('emails/email_verification', [
            'name' => $name,
            'verifyUrl' => $verifyUrl,
        ]);
        $apiKey = $this->brevoApiKey();

        if ($apiKey === '') {
            $this->logPasswordResetBrevoApiFailure('BREVO_API_KEY belum terbaca untuk email verifikasi.', [
                'from' => $fromEmail,
            ]);
            return false;
        }

        return $this->sendPasswordResetEmailViaBrevoApi(
            $apiKey,
            $email,
            $name,
            $fromEmail,
            $fromName,
            'Verifikasi email AdaAcara',
            $html,
            $verifyUrl
        );
    }

    private function brevoApiKey(): string
    {
        foreach (['BREVO_API_KEY', 'brevo.apiKey', 'brevo.api_key', 'email.brevoApiKey'] as $key) {
            $value = trim((string) (env($key, '') ?: getenv($key) ?: ($_ENV[$key] ?? '') ?: ($_SERVER[$key] ?? '')));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function sendPasswordResetEmailViaBrevoApi(string $apiKey, string $email, string $name, string $fromEmail, string $fromName, string $subject, string $html, string $resetUrl): bool
    {
        if ($fromEmail === '') {
            $this->logPasswordResetBrevoApiFailure('Konfigurasi Brevo API belum lengkap: from email kosong.');
            return false;
        }

        try {
            $client = service('curlrequest', [
                'timeout' => 15,
                'http_errors' => false,
            ]);

            $response = $client->post('https://api.brevo.com/v3/smtp/email', [
                'headers' => [
                    'accept' => 'application/json',
                    'api-key' => $apiKey,
                    'content-type' => 'application/json',
                ],
                'json' => [
                    'sender' => [
                        'name' => $fromName,
                        'email' => $fromEmail,
                    ],
                    'to' => [
                        [
                            'email' => $email,
                            'name' => $name,
                        ],
                    ],
                    'subject' => $subject,
                    'htmlContent' => $html,
                    'textContent' => "Reset password AdaAcara: {$resetUrl}",
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode < 200 || $statusCode >= 300) {
                $this->logPasswordResetBrevoApiFailure('Brevo API menolak email reset password.', [
                    'status' => (string) $statusCode,
                    'body' => mb_substr((string) $response->getBody(), 0, 1000),
                    'from' => $fromEmail,
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $exception) {
            $this->logPasswordResetBrevoApiFailure('Brevo API exception: ' . $exception->getMessage(), [
                'from' => $fromEmail,
            ]);
            return false;
        }
    }

    private function sendPasswordResetEmailViaSmtp(object $emailConfig, string $email, string $fromEmail, string $fromName, string $subject, string $html): bool
    {
        $emailService = service('email');

        $emailService->initialize([
            'protocol' => (string) $emailConfig->protocol,
            'SMTPHost' => (string) $emailConfig->SMTPHost,
            'SMTPUser' => (string) $emailConfig->SMTPUser,
            'SMTPPass' => (string) $emailConfig->SMTPPass,
            'SMTPPort' => (int) $emailConfig->SMTPPort,
            'SMTPTimeout' => 15,
            'SMTPCrypto' => (string) $emailConfig->SMTPCrypto,
            'mailType' => 'html',
            'charset' => (string) ($emailConfig->charset ?: 'UTF-8'),
            'newline' => "\r\n",
            'CRLF' => "\r\n",
        ]);

        if ($fromEmail === '' || (string) $emailConfig->SMTPHost === '' || (string) $emailConfig->SMTPUser === '' || (string) $emailConfig->SMTPPass === '') {
            $this->logPasswordResetSmtpFailure($emailConfig, $fromEmail, 'Konfigurasi SMTP reset password belum lengkap.');
            return false;
        }

        if (! $this->canConnectToSmtpHost($emailConfig, $fromEmail)) {
            return false;
        }

        if ($fromEmail !== '') {
            $emailService->setFrom($fromEmail, $fromName);
        }

        $emailService->setTo($email);
        $emailService->setSubject($subject);
        $emailService->setMailType('html');
        $emailService->setMessage($html);

        try {
            if (! $emailService->send(false)) {
                $this->logPasswordResetSmtpFailure(
                    $emailConfig,
                    $fromEmail,
                    'Email reset password gagal dikirim ke ' . $email,
                    $emailService->printDebugger(['headers', 'subject'])
                );
                return false;
            }
        } catch (\Throwable $exception) {
            $this->logPasswordResetSmtpFailure(
                $emailConfig,
                $fromEmail,
                'Email reset password exception untuk ' . $email . ': ' . $exception->getMessage()
            );
            throw $exception;
        }

        return true;
    }

    private function canConnectToSmtpHost(object $emailConfig, string $fromEmail): bool
    {
        $host = (string) ($emailConfig->SMTPHost ?? '');
        $port = (int) ($emailConfig->SMTPPort ?? 0);
        $errno = 0;
        $errstr = '';

        if ($host === '' || $port <= 0) {
            $this->logPasswordResetSmtpFailure($emailConfig, $fromEmail, 'Host atau port SMTP kosong.');
            return false;
        }

        $connection = @fsockopen($host, $port, $errno, $errstr, 10);
        if (! is_resource($connection)) {
            $this->logPasswordResetSmtpFailure(
                $emailConfig,
                $fromEmail,
                'Koneksi SMTP gagal: ' . $errno . ' ' . $errstr
            );
            return false;
        }

        fclose($connection);
        return true;
    }

    private function logPasswordResetSmtpFailure(object $emailConfig, string $fromEmail, string $message, string $debug = ''): void
    {
        log_message('error', '{message}. SMTP host={host}, user={user}, port={port}, crypto={crypto}, from={from}, pass_set={passSet}. Debug: {debug}', [
            'message' => $message,
            'host' => (string) ($emailConfig->SMTPHost ?? ''),
            'user' => (string) ($emailConfig->SMTPUser ?? ''),
            'port' => (string) ($emailConfig->SMTPPort ?? ''),
            'crypto' => (string) ($emailConfig->SMTPCrypto ?? ''),
            'from' => $fromEmail,
            'passSet' => (string) (($emailConfig->SMTPPass ?? '') !== '' ? 'yes' : 'no'),
            'debug' => trim(strip_tags($debug)),
        ]);
    }

    private function logPasswordResetBrevoApiFailure(string $message, array $context = []): void
    {
        log_message('error', '{message}. Brevo API key_set={keySet}, status={status}, from={from}, body={body}', [
            'message' => $message,
            'keySet' => $this->brevoApiKey() !== '' ? 'yes' : 'no',
            'status' => (string) ($context['status'] ?? '-'),
            'from' => (string) ($context['from'] ?? ''),
            'body' => (string) ($context['body'] ?? ''),
        ]);
    }
}
