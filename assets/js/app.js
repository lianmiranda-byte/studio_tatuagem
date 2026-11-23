document.addEventListener('DOMContentLoaded', () => {
    // Filtro instantâneo para qualquer tabela que tenha um input [data-table-search]
    const searchInputs = document.querySelectorAll('[data-table-search]');
    searchInputs.forEach(input => {
        const targetSelector = input.getAttribute('data-table-search');
        const table = document.querySelector(targetSelector);
        if (!table) return;

        input.addEventListener('input', () => {
            const term = input.value.toLowerCase().trim();
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    });

    // Auto-fechar alertas flash após 5 segundos
    setTimeout(() => {
        document.querySelectorAll('.alert-dismissible').forEach(alert => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        });
    }, 5000);
});