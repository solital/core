<?php

namespace Solital\Core\Auth\Trait;

trait PasswordPolicy
{
    private int $minimumLength = 8;
    private int $maximumLength = 64;
    private bool $requireUppercase = true;
    private bool $requireLowercase = true;
    private bool $requireDigits = true;
    private bool $requireSpecialChars = false;
    private int $passwordExpirationDays = 0; // 0 means no expiration
    private int $passwordHistorySize = 5;
    private string $specialChars = '!@#$%^&*()_+-=[]{}|;:\'",./<>?'; // Default special characters
    private array $customErrorMessages = []; // Store custom error messages

    /**
     * Set password minimum length
     *
     * @param int $length
     * 
     * @return self
     */
    public function setMinimumLength(int $length): self
    {
        $this->minimumLength = $length;
        return $this;
    }

    /**
     * Set password maximum length
     *
     * @param mixed $length
     * 
     * @return self
     */
    public function setMaximumLength($length): self
    {
        $this->maximumLength = $length;
        return $this;
    }

    /**
     * Require password uppercase
     *
     * @return self
     */
    public function requireUppercase(): self
    {
        $this->requireUppercase = true;
        return $this;
    }

    /**
     * Require password lowercase
     *
     * @return self
     */
    public function requireLowercase(): self
    {
        $this->requireLowercase = true;
        return $this;
    }

    /**
     * Require password digits
     *
     * @return self
     */
    public function requireDigits(): self
    {
        $this->requireDigits = true;
        return $this;
    }

    /**
     * Require password special chars
     *
     * @return self
     */
    public function requireSpecialChars(): self
    {
        $this->requireSpecialChars = true;
        return $this;
    }

    /**
     * Set password special chars
     *
     * @param string $specialChars
     * 
     * @return self
     */
    public function setSpecialChars(string $specialChars): self
    {
        $this->specialChars = $specialChars;
        return $this;
    }

    /**
     * Set password expiration days
     *
     * @param int $days
     * 
     * @return self
     */
    public function setPasswordExpirationDays(int $days): self
    {
        $this->passwordExpirationDays = $days;
        return $this;
    }

    /**
     * Set password history size
     *
     * @param int $size
     * 
     * @return self
     */
    public function setPasswordHistorySize(int $size): self
    {
        $this->passwordHistorySize = $size;
        return $this;
    }

    /**
     * Set custom error messages
     *
     * @param array $customErrorMessages
     * 
     * @return self
     */
    public function setCustomErrorMessages(array $customErrorMessages): self
    {
        $this->customErrorMessages = $customErrorMessages;
        return $this;
    }

    /**
     * Policy validation logic
     *
     * @param string $password
     * 
     * @return array|true 
     */
    public function validatePassword(string $password): array|true
    {
        $errors = [];

        // Minimum length check
        if (strlen($password) < $this->minimumLength) $errors[] = $this->getErrorText('min_length');

        // Maximum length check
        if (strlen($password) > $this->maximumLength) $errors[] = $this->getErrorText('max_length');

        // Uppercase letter requirement
        if ($this->requireUppercase && !preg_match('/[A-Z]/', $password))
            $errors[] = $this->getErrorText('uppercase');

        // Lowercase letter requirement
        if ($this->requireLowercase && !preg_match('/[a-z]/', $password))
            $errors[] = $this->getErrorText('lowercase');

        // Digit requirement
        if ($this->requireDigits && !preg_match('/[0-9]/', $password))
            $errors[] = $this->getErrorText('digits');

        // Special character requirement
        if ($this->requireSpecialChars && !$this->containsSpecialChars($password))
            $errors[] = $this->getErrorText('special_chars');

        // Additional policy checks can be added here
        if (!empty($errors)) return $errors; // Password does not meet policy requirements
        return true; // Password meets all policy requirements
    }

    /**
     * Helper function to get error text
     *
     * @param string $errorKey
     * 
     * @return string 
     */
    private function getErrorText(string $errorKey): string
    {
        // Check if a custom error message exists for the given error key
        if (isset($this->customErrorMessages[$errorKey])) return $this->customErrorMessages[$errorKey];

        // Default error messages
        $defaultErrorMessages = [
            'min_length' => "Password must be at least {$this->minimumLength} characters long.",
            'max_length' => "Password cannot exceed {$this->maximumLength} characters.",
            'uppercase' => "Password must contain at least one uppercase letter.",
            'lowercase' => "Password must contain at least one lowercase letter.",
            'digits' => "Password must contain at least one digit.",
            'special_chars' => "Password must contain at least one special character.",
            'password_expired' => "Your password has expired. Please reset it.",
            'password_history' => "Cannot reuse one of your last {$this->passwordHistorySize} passwords.",
            'account_locked' => "Your account is temporarily locked due to too many failed login attempts. Please try again later.",
            '2fa_required' => "Two-factor authentication is required for added security.",
            // ... (other default error messages)
        ];

        // Use the default error message if no custom message is specified
        return $defaultErrorMessages[$errorKey];
    }

    /**
     * Helper function to check for special characters
     *
     * @param string $password
     * 
     * @return int|false
     */
    private function containsSpecialChars(string $password): int|false
    {
        return preg_match("/[" . preg_quote($this->specialChars, '/') . "]/", $password);
    }
}
