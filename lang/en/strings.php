<?php

return [
    'fields' => [
        'first_name' => [
            'label' => "First name",
            'placeholder' => "Enter your first name",
        ],
        'last_name' => [
            'label' => "Last name",
            'placeholder' => "Enter your last name",
        ],
        'name' => [
            'label' => "Name",
            'placeholder' => "Enter your name",
        ],
        'email' => [
            'label' => "Email Address",
            'placeholder' => "Enter your email"
        ],
        'password' => [
            'label' => "Password"
        ],
        'current_password' => [
            'label' => "Current Password"
        ],
        'new_password' => [
            'label' => "New Password"
        ],
        'new_password_confirmation' => [
            'label' => "Confirm New Password"
        ],
        'password_confirmation' => [
            'label' => "Confirm Password",
        ],
        'remember_me' => [
            'label' => "Remember Me"
        ]
    ],
    'login' => [
        'seo_title' => 'Login',
        'title' => "Login",
        'forgot_password_link' => "Forgotten password",
        'socialite' => [
            'google' => "Login with Google",
            'github' => "Login with Github",
            'facebook' => "Login with Facebook",
        ]
    ],
    'logged_out' => "Logged out",
    'credentials_incorrect' => "The provided credentials do not match our records.",
    'forgotten_password' => [
        'seo_title' => "Forgotten Password",
    ],
    'password_reset_link_sent' => [ 
        'seo_title' => "Password Reset Link Sent"
    ],
    'reset_password' => [
        'seo_title' => "Reset Password"
    ],
    'account_recovery_success' => [
        'seo_title' => "Account Recovery Success",
    ],
    'password_reset_token' => [
        'expired' => [
            'seo_title' => "Password Reset Link Expired",
            'message' => "Your password reset link has expired. Please request a new one.",
        ],
        'invalid' => [
            'seo_title' => "Invalid Password Reset Link",
            'message' => "The password reset link is invalid or has been used.",
        ],
    ],
    'register' => [
        'seo_title' => "Create your account",
    ],
    'password_reset_email' => [
        'subject' => "Password Reset",
        'title' => "Password Reset Request",
        'greeting' => "Hello :name,",
        'line_1' => "You are receiving this email because we received a password reset request for your account.",
        'line_2' => "Click the button below to reset your password. This password reset link will expire in 60 minutes.",
        'button_label' => "Reset Password",
        'line_3' => "If you did not request a password reset, no further action is required.",
        'sign_off' => "Thanks,",
        'link_help' => "If you're having trouble clicking the 'Reset Password' button, copy and paste the URL below into your web browser:"
    ],
    'account' => [
        'seo_title' => "My Account",
        'details' => [
            'title' => "My Details",
            'update-success' => "Account details updated successfully.",
            'button_label' => "Update My Details",
        ],
        'password' => [
            'title'=> "My Password",
            'update-success' => "Password updated successfully.",
            'update-error' => "Something went wrong while updating your password. Please try again later.",
            'incorrect' => "The :attribute is incorrect.",
            'must-not-match' => "The :attribute must not be the same as your current password.",
            'button_label' => "Update password"
        ]
    ]
];