(function () {
    const form = document.querySelector('[data-lead-form]');

    if (!form) {
        return;
    }

    const alertBox = form.querySelector('.form-alert-js');
    const submitButton = form.querySelector('.form-submit');

    const setAlert = (type, message) => {
        if (!alertBox) {
            return;
        }

        alertBox.hidden = false;
        alertBox.className = `form-alert form-alert-js form-alert-${type}`;
        alertBox.textContent = message;
    };

    const clearErrors = () => {
        form.querySelectorAll('[aria-invalid="true"]').forEach((field) => {
            field.removeAttribute('aria-invalid');
        });
        form.querySelectorAll('.field-error[data-js-error]').forEach((error) => {
            error.remove();
        });
    };

    const addFieldError = (name, message) => {
        const field = form.elements[name];

        if (!field) {
            return;
        }

        field.setAttribute('aria-invalid', 'true');

        const wrapper = field.closest('.field') || field.closest('.checkbox') || field.parentElement;
        const error = document.createElement('p');
        error.className = 'field-error';
        error.dataset.jsError = 'true';
        error.textContent = message;
        wrapper.insertAdjacentElement('afterend', error);
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors();

        if (alertBox) {
            alertBox.hidden = true;
        }

        submitButton.disabled = true;
        submitButton.textContent = 'Отправляем...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await response.json();

            if (!response.ok || !data.ok) {
                const errors = data.errors || {};
                Object.entries(errors).forEach(([name, message]) => {
                    if (name === 'form') {
                        setAlert('error', message);
                    } else {
                        addFieldError(name, message);
                    }
                });

                if (!errors.form) {
                    setAlert('error', 'Проверьте поля формы и отправьте заявку еще раз.');
                }

                return;
            }

            setAlert('success', data.message || 'Заявка отправлена.');
            form.reset();

            if (data.csrfToken && form.elements.csrf_token) {
                form.elements.csrf_token.value = data.csrfToken;
            }
        } catch (error) {
            setAlert('error', 'Не удалось отправить заявку. Проверьте соединение или позвоните нам.');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Получить расчет';
        }
    });
})();
