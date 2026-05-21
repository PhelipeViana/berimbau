document.addEventListener('input', function (event) {
    const input = event.target;
    if (!input.classList || !input.classList.contains('input-date-br')) {
        return;
    }

    const digits = input.value.replace(/\D/g, '').slice(0, 8);
    const parts = [];

    if (digits.length > 0) {
        parts.push(digits.slice(0, 2));
    }
    if (digits.length > 2) {
        parts.push(digits.slice(2, 4));
    }
    if (digits.length > 4) {
        parts.push(digits.slice(4, 8));
    }

    input.value = parts.join('/');
});
