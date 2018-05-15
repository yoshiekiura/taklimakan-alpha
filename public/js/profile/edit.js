'use strict';

(function () {
    $(function () {
        const $form = $('form[name=profile]');
        const $firstName = $('#profile_first_name');
        const $lastName = $('#profile_last_name');
        const $token = $('#profile_erc20_token');
        const $password = $('#profile_password_first');
        const $repeatedPassword = $('#profile_password_second');
        const $passwordHelp = $('#passwordHelp');

        $form.submit(function () {
            let hasError = false;
            if (!checkFirstName()) {
                hasError = true;
            }
            if (!checkLastName()) {
                hasError = true;
            }
            if (!checkToken()) {
                hasError = true;
            }
            if (!checkPassword()) {
                hasError = true;
            }
            if (!checkRepeatedPassword()) {
                hasError = true;
            }
            return !hasError;
        });

        function checkFirstName() {
            if ($firstName.val().trim().length === 0) {
                setError($firstName, 'Required');
                return false;
            }
            setSuccess($firstName);
            return true;
        }

        function checkLastName() {
            if ($lastName.val().trim().length === 0) {
                setError($lastName, 'Required');
                return false;
            }
            setSuccess($lastName);
            return true;
        }

        function checkToken() {
            const token = $token.val().trim();
            if (token.length !== 0) {
                const w3 = new Web3();
                if (!w3.isAddress(token)) {
                    setError($token, 'Invalid wallet');
                    return false;
                }
            }
            setSuccess($token);
            return true;
        }

        function checkPassword() {
            const password = $password.val().trim();
            if (password.length === 0) {
                return true;
            }
            if (password.length < 8 || !(/^[0-9a-zA-Z$&+,:;=?@#|'<>.-^*()%!]*$/.test(password)) || !(/[a-zA-Z]+/.test(password)) || !(/[0-9]+/.test(password))) {
                $passwordHelp.addClass('valid-error');
                $password.closest('.form-group').removeClass('checking').addClass('wrong');
                return false;
            }
            $password.closest('.form-group').removeClass('wrong').addClass('checking');
            $passwordHelp.removeClass('valid-error');
            return true;
        }

        function checkRepeatedPassword() {
            if ($repeatedPassword.val() !== $password.val()) {
                setError($repeatedPassword, 'Passwords do not match');
                return false;
            }
            if ($password.val().length === 0) {
                clearError($repeatedPassword);
                return true;
            }
            setSuccess($repeatedPassword);
            return true;
        }

        function setSuccess($field) {
            $field.closest('.form-group')
                .removeClass('wrong')
                .addClass('checking')
                .find('.valid-error')
                .hide()
            ;
        }

        function setError($field, message) {
            $field.closest('.form-group')
                .removeClass('checking')
                .addClass('wrong')
                .find('.valid-error')
                .text(message)
                .show()
            ;
        }

        function clearError($field) {
            $field.closest('.form-group')
                .removeClass('checking')
                .removeClass('wrong')
                .find('.valid-error')
                .hide()
            ;
        }
    });
}());
