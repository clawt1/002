<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Session;
use App\Helpers\Validator;
use App\Helpers\Flash;
use App\Helpers\Mailer;
use App\Models\User;

class AuthController extends Controller {
    public function showLogin() {
        if (Auth::check()) {
            $this->redirect('/');
        }
        $this->view('auth/login');
    }

    public function login() {
        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide. Veuillez réessayer.");
            $this->redirect('/login');
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $res = Auth::attempt($email, $password);
        if ($res === true) {
            $url = Session::get('redirect_after_login') ?: '/';
            Session::remove('redirect_after_login');
            Flash::success("Connexion réussie !");
            $this->redirect($url);
        } elseif ($res === 'rate_limit') {
            Flash::error("Trop de tentatives de connexion. Veuillez patienter 15 minutes.");
            $this->redirect('/login');
        } else {
            Flash::error("Identifiants incorrects ou compte inactif.");
            $this->redirect('/login');
        }
    }

    public function showRegister() {
        if (Auth::check()) {
            $this->redirect('/');
        }
        $this->view('auth/register');
    }

    public function register() {
        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect('/register');
        }

        $validator = new Validator();
        $validated = $validator->validate($_POST, [
            'nom' => ['required', 'min:2', 'max:50'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:4']
        ]);

        if (!$validated) {
            $errors = $validator->getErrors();
            foreach ($errors as $field => $errs) {
                foreach ($errs as $err) {
                    Flash::error($err);
                }
            }
            $this->redirect('/register');
        }

        $userModel = new User();
        $userModel->create([
            'nom' => $_POST['nom'],
            'email' => $_POST['email'],
            'mot_de_passe_hash' => password_hash($_POST['password'], PASSWORD_BCRYPT),
            'role_id' => 3,
            'actif' => 1
        ]);

        Flash::success("Inscription réussie ! Vous pouvez maintenant vous connecter.");
        $this->redirect('/login');
    }

    public function showForgotPassword() {
        $this->view('auth/forgot-password');
    }

    public function forgotPassword() {
        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect('/forgot-password');
        }

        $email = $_POST['email'] ?? '';
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user) {
            $resetToken = bin2hex(random_bytes(16));
            Session::set('reset_token', $resetToken);
            Session::set('reset_email', $email);
            Session::set('reset_expires', time() + 3600);

            $config = require __DIR__ . '/../Config/config.php';
            $resetLink = rtrim($config['app']['url'], '/') . "/reset-password?token=" . $resetToken;

            $subject = "Réinitialisation de votre mot de passe";
            $body = "<h2>Roadmap Manager</h2><p>Bonjour {$user['nom']},</p><p>Pour réinitialiser votre mot de passe, cliquez sur ce lien :</p><p><a href='{$resetLink}'>{$resetLink}</a></p>";

            Mailer::send($email, $subject, $body);
        }

        Flash::success("Si cette adresse email est connue, un message de réinitialisation vous a été envoyé. Vérifiez également le fichier /app/logs/emails.log.");
        $this->redirect('/forgot-password');
    }

    public function showResetPassword() {
        $token = $_GET['token'] ?? '';
        $storedToken = Session::get('reset_token');
        $expires = Session::get('reset_expires');

        if (!$token || !$storedToken || !hash_equals($storedToken, $token) || time() > $expires) {
            Flash::error("Lien de réinitialisation invalide ou expiré.");
            $this->redirect('/forgot-password');
        }

        $this->view('auth/reset-password', ['token' => $token]);
    }

    public function resetPassword() {
        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            Flash::error("Jeton CSRF invalide.");
            $this->redirect('/forgot-password');
        }

        $urlToken = $_POST['token'] ?? '';
        $storedToken = Session::get('reset_token');
        $expires = Session::get('reset_expires');
        $email = Session::get('reset_email');

        if (!$urlToken || !$storedToken || !hash_equals($storedToken, $urlToken) || time() > $expires || !$email) {
            Flash::error("Lien de réinitialisation invalide ou expiré.");
            $this->redirect('/forgot-password');
        }

        $password = $_POST['password'] ?? '';
        if (strlen($password) < 4) {
            Flash::error("Le mot de passe doit faire au moins 4 caractères.");
            $this->redirect('/reset-password?token=' . $urlToken);
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);
        if ($user) {
            $userModel->updatePassword($user['id'], password_hash($password, PASSWORD_BCRYPT));
            Session::remove('reset_token');
            Session::remove('reset_email');
            Session::remove('reset_expires');
            Flash::success("Votre mot de passe a été réinitialisé avec succès !");
            $this->redirect('/login');
        }

        Flash::error("Une erreur est survenue.");
        $this->redirect('/forgot-password');
    }

    public function logout() {
        Auth::logout();
        Flash::success("Vous avez été déconnecté.");
        $this->redirect('/login');
    }
}
