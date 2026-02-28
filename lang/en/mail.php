<?php

declare(strict_types=1);

return [
    'workspace' => [
        'ready' => [
            'subject' => 'Your workspace is ready',
            'title' => 'Your :workspace workspace is ready',
            'description' => 'Finish setting up your account to access your new workspace.',
            'cta' => 'Set up your account',
            'button_note' => 'If the button does not work, you can copy and paste the URL below into your browser.',
            'footer' => 'If you did not request this workspace, you can safely ignore this email.',
        ],
    ],
    'password_reset' => [
        'subject' => 'Reset your password',
        'title' => 'Reset your password',
        'description' => 'We received a request to reset your password. Use the button below to set a new password.',
        'cta' => 'Reset password',
        'button_note' => 'If the button does not work, copy and paste this URL into your browser:',
        'footer' => 'If you did not request a password reset, you can safely ignore this email.',
    ],
    'email_verification' => [
        'subject' => 'Verify your email address',
        'title' => 'Verify your email address',
        'description' => 'Please confirm your email address to complete your account setup.',
        'cta' => 'Verify email',
        'button_note' => 'If the button does not work, copy and paste this URL into your browser:',
        'footer' => 'If you did not create an account, you can safely ignore this email.',
    ],
    'invite' => [
        'subject' => 'You have been invited to join a workspace',
        'title' => 'You are invited to join a workspace',
        'description' => 'Complete your account setup to access your workspace.',
        'cta' => 'Accept invitation',
        'button_note' => 'If the button does not work, copy and paste this URL into your browser:',
        'footer' => 'If you did not expect this invitation, you can safely ignore this email.',
    ],
];
