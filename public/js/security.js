'use strict';

$(function () {
    const $registrationForm = $('#registration-form');
    const $firstName = $('#registration_first_name');
    const $lastName = $('#registration_last_name');
    const $email = $('#registration_email');
    const $token = $('#registration_erc20_token');
    const $password = $('#registration_password_first');
    const $passwordHelp = $('#passwordHelp');
    const $repeatedPassword =  $('#registration_password_second');
    const $confirmationCodeForm = $('#confirmation-code-form');

    /*$firstName.on('change', function () {
        checkFirstName();
    });
    $firstName.on('keyup', function () {
        checkFirstName();
    });
    $firstName.on('keydown', function () {
        checkFirstName();
    });

    $lastName.on('change', function () {
        checkLastName();
    });
    $lastName.on('keyup', function () {
        checkLastName();
    });
    $lastName.on('keydown', function () {
        checkLastName();
    });

    $email.on('change', function () {
        checkEmail();
    });
    $email.on('keyup', function () {
        checkEmail();
    });
    $email.on('keydown', function () {
        checkEmail();
    });

    $token.on('change', function () {
        checkToken();
    });
    $token.on('keyup', function () {
        checkToken();
    });
    $token.on('keydown', function () {
        checkToken();
    });

    $password.on('change', function () {
        checkPassword();
    });
    $password.on('keyup', function () {
        checkPassword();
    });
    $password.on('keydown', function () {
        checkPassword();
    });

    $repeatedPassword.on('change', function () {
        checkRepeatedPassword();
    });
    $repeatedPassword.on('keyup', function () {
        checkRepeatedPassword();
    });
    $repeatedPassword.on('keydown', function () {
        checkRepeatedPassword();
    });*/

    $registrationForm.submit(function () {
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
        if (!checkEmail()) {
            hasError = true;
        }
        if (!checkPassword()) {
            hasError = true;
        }
        if (!checkRepeatedPassword()) {
            hasError = true;
        }
        if (!hasError) {
            $.post($registrationForm.attr('action'), $registrationForm.serialize(), function (response) {
                if (!response.success) {
                    if (response.exists_email) {
                        setError($email, 'User with this email already exists');
                    }
                    return;
                }
                $('#register').modal('hide');
                // $('#registrationCode').attr('placeholder', response.code);
                $('#email-ver').modal();
            });
        }
        return false;
    });

    $confirmationCodeForm.submit(function () {
        $.post($confirmationCodeForm.attr('action'), {
            code: $('#registrationCode').val()
        }, function (response) {
            if (!response.success) {
                setError($('#registrationCode'), 'Invalid confirmation code');
                return;
            }
            window.location.href = '/profile';
        });
        return false;
    });

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

    function checkFirstName() {
        if ($firstName.val().length === 0) {
            setError($firstName, 'Required');
            return false;
        }
        setSuccess($firstName);
        return true;
    }

    function checkLastName() {
        if ($lastName.val().length === 0) {
            setError($lastName, 'Required');
            return false;
        }
        setSuccess($lastName);
        return true;
    }

    function checkToken() {
        if ($token.val().length !== 67 || !(/^0x[a-zA-Z0-9]{65}$/.test($token.val()))) {
            setError($token, 'Invalid wallet');
            return false;
        }
        setSuccess($token);
        return true;
    }

    function checkEmail() {
        if (!validateEmail($email.val())) {
            setError($email, 'Enter correct email address');
            return false;
        }
        setSuccess($email);
        return true;
    }

    function checkPassword() {
        const password = $password.val();
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

    function validateEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }

    (function () {
        const $form = $('#login_form');
        $form.submit(function () {
            $.post($form.attr('action'), $form.serialize(), function (response) {
                if (!response.success) {
                    setError($('#login_email'));
                    setError($('#login_password'), 'User with that email and password not found');
                    return;
                }
                window.location.href = '/profile';
            });
            return false;
        });
    }());
});
